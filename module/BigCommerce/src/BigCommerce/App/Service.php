<?php
namespace BigCommerce\App;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreationService;
use CG\BigCommerce\Client\Factory as BigCommerceClientFactory;
use CG\BigCommerce\Client\Signer as BigCommerceClientSigner;
use CG\OrganisationUnit\Service as OUService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG_Login\Service as LoginService;
use CG_Register\Service as RegisterService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_MISSING_OAUTH_PARAMS = 'OAuth request did not include all expected parameters';
    const LOG_MSG_MISSING_OAUTH_PARAMS = 'OAuth request did not include all expected parameters';
    const LOG_CODE_MISSING_SCOPES = 'OAuth request does not include all required scopes';
    const LOG_MSG_MISSING_SCOPES = 'OAuth request does not include all required scopes - Missing scopes: ';
    const LOG_CODE_INVALID_SHOP_CONTEXT = 'OAuth request does not have a valid store context';
    const LOG_MSG_INVALID_SHOP_CONTEXT = 'OAuth request does not have a valid store context - %s';
    const LOG_CODE_MISSING_SHOP_HASH = 'Signed payload does not include a store hash';
    const LOG_MSG_MISSING_SHOP_HASH = 'Signed payload does not include a store hash';
    const LOG_CODE_MISSING_USER_ID = 'Signed payload does not include a userId';
    const LOG_MSG_MISSING_USER_ID = 'Signed payload does not include a userId';

    /** @var LoginService $loginService */
    protected $loginService;
    /** @var RegisterService $registerService */
    protected $registerService;
    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var BigCommerceAccountCreationService $accountCreationService */
    protected $accountCreationService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OUService $ouService */
    protected $ouService;
    /** @var BigCommerceClientFactory $clientFactory */
    protected $clientFactory;
    /** @var BigCommerceClientSigner $clientSigner */
    protected $clientSigner;
    /** @var UserService $userService */
    protected $userService;
    /** @var TokenService $tokenService */
    protected $tokenService;

    public function __construct(
        LoginService $loginService,
        RegisterService $registerService,
        ActiveUserInterface $activeUser,
        ViewModelFactory $viewModelFactory,
        BigCommerceAccountCreationService $accountCreationService,
        AccountService $accountService,
        OUService $ouService,
        BigCommerceClientFactory $clientFactory,
        BigCommerceClientSigner $clientSigner,
        UserService $userService,
        TokenService $tokenService
    ) {
        $this
            ->setLoginService($loginService)
            ->setRegisterService($registerService)
            ->setActiveUser($activeUser)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountCreationService($accountCreationService)
            ->setAccountService($accountService)
            ->setOuService($ouService)
            ->setClientFactory($clientFactory)
            ->setClientSigner($clientSigner)
            ->setUserService($userService)
            ->setTokenService($tokenService);
    }

    public function saveProgressAndRedirectToLogin(MvcEvent $event, $route, array $routeParams = [], array $routeOptions = [])
    {
        $this->loginService->setLandingRoute($route, $routeParams, $routeOptions);
        return $this->loginService->loginRedirect($event);
    }

    /**
     * @return Account
     */
    public function processOauth($redirectUri, array $parameters)
    {
        if (!$this->activeUser->getActiveUser()) {
            throw new LoginException('User is not logged in');
        }

        $shopHash = null;
        if (isset($parameters['shopHash'])) {
            $shopHash = $parameters['shopHash'];
        } else if (isset($parameters['context'])) {
            $shopHash = $this->getShopHash($parameters['context']);
        }

        $accountParameters = ['shopHash' => $shopHash, 'redirectUri' => $redirectUri];
        if ($token = $this->tokenService->fetchToken($shopHash, $additionalInfo)) {
            $accountParameters['accessToken'] = $token;
            if (isset($additionalInfo['parameters'])) {
                $parameters = $additionalInfo['parameters'];
            }
            if (isset($additionalInfo['response'])) {
                $accountParameters['response'] = $additionalInfo['response'];
            }
        }

        $this->validateOauthParameters($parameters);
        $account = $this->accountCreationService->connectAccount(
            $this->activeUser->getCompanyId(),
            $this->getAccountId($shopHash),
            array_merge($parameters, $accountParameters)
        );

        if ($bigCommerceUserId = $this->accountCreationService->getBigCommerceUserId()) {
            $this->userService->registerUserAssociation($bigCommerceUserId, $this->activeUser->getActiveUser());
        }

        return $account;
    }

    public function hasCachedOauthRequest($shopHash)
    {
        return $this->tokenService->hasToken($shopHash);
    }

    public function cacheOauthRequest($redirectUri, array $parameters)
    {
        $this->validateOauthParameters($parameters);
        $shopHash = $this->getShopHash($parameters['context']);
        $client = $this->clientFactory->createSimpleClient($shopHash);

        $accessToken = BigCommerceAccountCreationService::getAccessToken(
            $client,
            array_merge(['redirectUri' => $redirectUri], $parameters),
            $response
        );
        $this->tokenService->storeToken($shopHash, $accessToken, ['parameters' => $parameters, 'response' => $response]);

        if (!isset($response['user']['email'])) {
            return;
        }

        $firstName = $lastName = null;
        if (preg_match('/^(?<firstName>[^@\+\.]+)\.(?<lastName>[^@\+]+)/', $response['user']['email'], $match)) {
            $firstName = ucfirst(strtolower($match['firstName']));
            $lastName = ucwords(strtolower(str_replace('.', ' ', $match['lastName'])));
        }

        try {
            $storeInformation = $client->setToken($accessToken)->getStoreInformation();
        } catch (\Exception $exception) {
            // Ignore all errors - this is more a nice to have that a requirement
            $storeInformation = [];
        }

        $this->loginService->setUsername($response['user']['email']);
        $this->registerService->setUserData(
            [
                'email' => $response['user']['email'],
                'ouName' => isset($storeInformation['name']) ? $storeInformation['name'] : null,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'telephone' => isset($storeInformation['phone']) ? $storeInformation['phone'] : null,
                'address' => isset($storeInformation['address']) ? $storeInformation['address'] : null,
            ]
        );
    }

    /**
     * @return Account
     */
    public function processLoadRequest($signedPayload, &$shopHash = null)
    {
        $data = $this->clientSigner->getDataFromSignedPayload($signedPayload);
        $this->loginUserForPayload($data);

        if (!isset($data['store_hash'])) {
            $this->logPrettyDebug(static::LOG_MSG_MISSING_SHOP_HASH, $data, [], static::LOG_CODE_MISSING_SHOP_HASH);
            throw new \InvalidArgumentException(static::LOG_CODE_MISSING_SHOP_HASH);
        }

        $shopHash = $data['store_hash'];
        return $this->getAccount($shopHash);
    }

    protected function validateOauthParameters(array $parameters)
    {
        if (!isset($parameters['code'], $parameters['scope'], $parameters['context'])) {
            $this->logPrettyDebug(static::LOG_MSG_MISSING_OAUTH_PARAMS, array_merge(['code' => '-', 'scope' => '-', 'context' => '-'], $parameters), [], static::LOG_CODE_MISSING_OAUTH_PARAMS);
            throw new \InvalidArgumentException(static::LOG_CODE_MISSING_OAUTH_PARAMS);
        }

        $this->validateScope(
            is_array($parameters['scope']) ? $parameters['scope'] : explode(' ', $parameters['scope'])
        );
    }

    protected function validateScope(array $scopes)
    {
        $missingScopes = [];
        $requiredScopes = [
            'store_v2_default' => true,
            'store_v2_information' => false,
            'store_v2_customers' => false,
            'store_v2_orders' => true,
            'store_v2_products' => true,
        ];

        $scopes = array_fill_keys($scopes, true);
        foreach ($requiredScopes as $scope => $writeAccessRequired) {
            if (isset($scopes[$scope])) {
                continue;
            }
            if (!$writeAccessRequired && isset($scopes[$scope . '_read_only'])) {
                continue;
            }
            $missingScopes[$scope] = $writeAccessRequired ? 'write' : 'read_only';
        }

        if (!empty($missingScopes)) {
            $this->logPrettyDebug(static::LOG_MSG_MISSING_SCOPES, $missingScopes, [], static::LOG_CODE_MISSING_SCOPES);
            throw new \RuntimeException(static::LOG_CODE_MISSING_SCOPES);
        }
    }

    protected function getShopHash($context)
    {
        if (!preg_match('|^stores/(?<hash>.+)$|', $context, $store)) {
            $this->logDebug(static::LOG_MSG_INVALID_SHOP_CONTEXT, ['context' => $context], static::LOG_CODE_INVALID_SHOP_CONTEXT);
            throw new \InvalidArgumentException(static::LOG_CODE_INVALID_SHOP_CONTEXT);
        }
        return $store['hash'];
    }

    protected function loginUserForPayload(array $payload)
    {
        if ($this->activeUser->getActiveUser()) {
            return;
        }

        try {
            if (!isset($payload['user']['id'])) {
                $this->logPrettyAlert(static::LOG_MSG_MISSING_USER_ID, $payload, [], static::LOG_CODE_MISSING_USER_ID);
                throw new \InvalidArgumentException(static::LOG_CODE_MISSING_USER_ID);
            }

            $user = $this->userService->getAssociatedUser($payload['user']['id']);
            $this->loginService->loginAsUser($user);
        } catch (\Exception $exception) {
            $this->logException($exception, 'debug', __NAMESPACE__);
            if (isset($payload['user']['email'])) {
                $this->loginService->setUsername($payload['user']['email']);
                $this->registerService->setUserData(['email' => $payload['user']['email']]);
            }
            throw new LoginException('Failed to login user', 0, $exception);
        }
    }

    protected function getAccountId($shopHash)
    {
        try {
            return $this->getAccount($shopHash)->getId();
        } catch (NotFound $exception) {
            // No accounts match lookup - we're creating a new account
            return null;
        }
    }

    /**
     * @return Account
     */
    protected function getAccount($shopHash)
    {
        $filter = (new AccountFilter(1, 1))
            ->setChannel([BigCommerceAccountCreationService::CHANNEL])
            ->setExternalId([$shopHash])
            ->setOrganisationUnitId(
                $this->ouService->fetchRelatedOrganisationUnitIds($this->activeUser->getCompanyId())
            )
            ->setDeleted(false);

        /** @var Accounts $accounts */
        $accounts = $this->accountService->fetchByFilter($filter);
        $accounts->rewind();
        return $accounts->current();
    }

    /**
     * @return self
     */
    protected function setLoginService(LoginService $loginService)
    {
        $this->loginService = $loginService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setRegisterService(RegisterService $registerService)
    {
        $this->registerService = $registerService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setActiveUser(ActiveUserInterface $activeUser)
    {
        $this->activeUser = $activeUser;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountCreationService(BigCommerceAccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setOuService(OUService $ouService)
    {
        $this->ouService = $ouService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setClientFactory(BigCommerceClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setClientSigner(BigCommerceClientSigner $clientSigner)
    {
        $this->clientSigner = $clientSigner;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setTokenService(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        return $this;
    }
}
