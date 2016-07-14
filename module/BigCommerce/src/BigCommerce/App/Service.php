<?php
namespace BigCommerce\App;

use BigCommerce\Account\Session as BigCommerceAccountSession;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreationService;
use CG\OrganisationUnit\Service as OUService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_MISSING_OAUTH_PARAMS = 'OAuth request did not include all expected parameters';
    const LOG_MSG_MISSING_OAUTH_PARAMS = 'OAuth request did not include all expected parameters';
    const LOG_CODE_MISSING_SCOPES = 'OAuth request does not include all required scopes';
    const LOG_MSG_MISSING_SCOPES = 'OAuth request does not include all required scopes - Missing scopes: ';
    const LOG_CODE_INVALID_SHOP_CONTEXT = 'OAuth request does not have a valid store context';
    const LOG_MSG_INVALID_SHOP_CONTEXT = 'OAuth request does not have a valid store context - %s';

    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var BigCommerceAccountCreationService $accountCreationService */
    protected $accountCreationService;
    /** @var BigCommerceAccountSession $accountSession */
    protected $accountSession;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OUService $ouService */
    protected $ouService;

    public function __construct(
        ActiveUserInterface $activeUser,
        ViewModelFactory $viewModelFactory,
        BigCommerceAccountCreationService $accountCreationService,
        BigCommerceAccountSession $accountSession,
        AccountService $accountService,
        OUService $ouService
    ) {
        $this
            ->setActiveUser($activeUser)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountCreationService($accountCreationService)
            ->setAccountSession($accountSession)
            ->setAccountService($accountService)
            ->setOuService($ouService);
    }

    public function getAppView()
    {
        return $this->viewModelFactory->newInstance(
            [
                'isNavBarVisible' => false,
                'isHeaderBarVisible' => false,
                'isSidebarPresent' => false,
            ]
        )->setTemplate('bigcommerce/app.phtml');
    }

    public function processOauth($redirectUri, array $parameters)
    {
        if (!isset($parameters['code'], $parameters['scope'], $parameters['context'])) {
            $this->logPrettyDebug(static::LOG_MSG_MISSING_OAUTH_PARAMS, array_merge(['code' => '-', 'scope' => '-', 'context' => '-'], $parameters), [], static::LOG_CODE_MISSING_OAUTH_PARAMS);
            throw new \InvalidArgumentException(static::LOG_CODE_MISSING_OAUTH_PARAMS);
        }

        $this->validateScope(
            is_array($parameters['scope']) ? $parameters['scope'] : explode(' ', $parameters['scope'])
        );

        $shopHash = $this->getShopHash($parameters['context']);
        $this->accountCreationService->connectAccount(
            $this->activeUser->getCompanyId(),
            $this->getAccountId($shopHash),
            array_merge(['shopHash' => $shopHash, 'redirectUri' => $redirectUri], $parameters)
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
        if (preg_match('|^stores/(?<hash>.+)$|', $context, $store)) {
            return $store['hash'];
        }
        $this->logDebug(static::LOG_MSG_INVALID_SHOP_CONTEXT, ['context' => $context], static::LOG_CODE_INVALID_SHOP_CONTEXT);
        throw new \InvalidArgumentException(static::LOG_CODE_INVALID_SHOP_CONTEXT);
    }

    protected function getAccountId($shopHash)
    {
        $accountId = $this->accountSession->getAccountId($shopHash);
        if ($accountId) {
            return $accountId;
        }

        try {
            $filter = (new AccountFilter(1, 1))
                ->setChannel([BigCommerceAccountCreationService::CHANNEL])
                ->setExternalId([$shopHash])
                ->setOrganisationUnitId($this->ouService->fetchRelatedOrganisationUnitIds($this->activeUser->getCompanyId()))
                ->setDeleted(false);

            /** @var Accounts $accounts */
            $accounts = $this->accountService->fetchByFilter($filter);
            $accounts->rewind();

            /** @var Account $account */
            $account = $accounts->current();
            return $account->getId();
        } catch (NotFound $exception) {
            // No accounts match lookup - we're creating a new account
            return null;
        }
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
    protected function setAccountSession(BigCommerceAccountSession $accountSession)
    {
        $this->accountSession = $accountSession;
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
}
