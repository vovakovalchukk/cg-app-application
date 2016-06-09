<?php
namespace CG_Shopify\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG\Shopify\Account as ShopifyAccount;
use CG\Shopify\Client\Factory as ClientFactory;
use CG\Shopify\Client;
use CG\Shopify\Credentials;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use CG_Shopify\Controller\AccountController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use InvalidArgumentException;
use Zend\Session\Container as Session;
use Zend\Stdlib\ArrayObject;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_INVALID_OAUTH_RESPONSE = 'Invalid Shopify OAuth Response';
    const LOG_MSG_INVALID_OAUTH_RESPONSE = 'Expected Fields';
    const LOG_CODE_INVALID_NONCE = 'Invalid Shopify OAuth Nonce';
    const LOG_MSG_INVALID_NONCE = 'Nonce Mismatch';

    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var Cryptor $cryptor */
    protected $cryptor;
    /** @var UrlHelper $urlHelper */
    protected $urlHelper;
    /** @var ClientFactory $clientFactory */
    protected $clientFactory;
    /** @var Session $session */
    protected $session;
    /** @var ShopifyAccountCreator $shopifyAccountCreator */
    protected $shopifyAccountCreator;

    public function __construct(
        ActiveUserInterface $activeUser,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService,
        Cryptor $cryptor,
        UrlHelper $urlHelper,
        ClientFactory $clientFactory,
        Session $session,
        ShopifyAccountCreator $shopifyAccountCreator
    ) {
        $this
            ->setActiveUser($activeUser)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setAccountService($accountService)
            ->setCryptor($cryptor)
            ->setUrlHelper($urlHelper)
            ->setClientFactory($clientFactory)
            ->setSession($session)
            ->setShopifyAccountCreator($shopifyAccountCreator);
    }

    public function getSetupView($accountId = null, $cancelUrl = null)
    {
        $view = $this->viewModelFactory->newInstance(
            [
                'isHeaderBarVisible' => false,
                'isSidebarPresent' => false,
                'accountId' => $accountId,
                'submitUrl' => $this->getSubmitUrl(),
                'cancelUrl' => $cancelUrl,
            ]
        );

        $accountData = [];
        if ($accountId) {
            /** @var Account|null $account */
            $account = $this->accountService->fetch($accountId);
            /** @var Credentials|null $credentials */
            $credentials = $this->cryptor->decrypt($account->getCredentials());

            $accountData = $account->toArray();
            $accountData['credentials'] = $credentials->toArray();
            $view->setVariable('accountData', $accountData);
        } else {
            $view->setVariable('accountData', []);
        }

        return $view->setTemplate('cg_shopify/account/setup');
    }

    protected function getSubmitUrl()
    {
        $route = [ShopifyAccount::ROUTE_SHOPIFY, ShopifyAccount::ROUTE_SETUP, AccountController::ROUTE_SETUP_LINK];
        return $this->urlHelper->fromRoute(implode('/', $route));
    }

    public function getLinkJson($shop, $accountId = null)
    {
        $shop = $this->parseShop($shop);
        $client = $this->clientFactory->createClientForCredentials(new Credentials($shop));
        $redirectUrl = $client->getOauthLink($nonce, Client::getRequiredScopes(), $this->getProcessUrl($accountId));

        if (!isset($this->session['oauth']) || !($this->session['oauth'] instanceof ArrayObject)) {
            $this->session['oauth'] = new ArrayObject();
        }
        $this->session['oauth'][$shop] = $nonce;

        return $this->jsonModelFactory->newInstance(['redirectUrl' => $redirectUrl]);
    }

    protected function parseShop($shop)
    {
        if (filter_var($shop, FILTER_VALIDATE_URL)) {
            $shop = parse_url($shop, PHP_URL_HOST);
        }
        if (!preg_match('/[a-z0-9\.\-]\.myshopify\.com$/i', $shop)) {
            throw new InvalidArgumentException(sprintf('Shop (%s) is not a valid Shopify Shop', $shop));
        }
        return $shop;
    }

    protected function getProcessUrl($accountId = null)
    {
        $route = [ShopifyAccount::ROUTE_SHOPIFY, ShopifyAccount::ROUTE_SETUP, AccountController::ROUTE_SETUP_RETURN];
        return $this->urlHelper->fromRoute(
            implode('/', $route),
            [],
            [
                'force_canonical' => true,
                'query' => [
                    'accountId' => $accountId,
                ]
            ]
        );
    }

    /**
     * @return Account
     */
    public function activateAccount(array $parameters, $accountId = null)
    {
        if (!isset($parameters['shop'], $parameters['state'], $parameters['code'])) {
            $this->logPrettyError(static::LOG_MSG_INVALID_OAUTH_RESPONSE, ['shop' => isset($parameters['shop']) ? $parameters['shop'] : '-', 'state' => isset($parameters['state']) ? $parameters['state'] : '-', 'code' => isset($parameters['code']) ? $parameters['code'] : '-'], [], static::LOG_CODE_INVALID_OAUTH_RESPONSE);
            throw new InvalidArgumentException('Invalid OAuth response from Shopify');
        }

        $shop = $parameters['shop'];
        $nonce = $parameters['state'];
        $code = $parameters['code'];

        if (!isset($this->session['oauth'][$shop]) || $this->session['oauth'][$shop] != $nonce) {
            $this->logPrettyError(static::LOG_MSG_INVALID_NONCE, ['Session' => isset($this->session['oauth'][$shop]) ? $this->session['oauth'][$shop] : '-', 'OAuth' => $nonce], [], static::LOG_CODE_INVALID_NONCE);
            throw new InvalidArgumentException(
                sprintf("OAuth response has been comprimised:\n\"%s\" != \"%s\"", isset($this->session['oauth'][$shop]) ? $this->session['oauth'][$shop] : '-', $nonce)
            );
        }

        $client = $this->clientFactory->createClientForCredentials(new Credentials($shop));
        $client->hmacSignatureValidation($parameters);
        $token = $client->getToken($code, Client::getRequiredScopes());

        return $this->shopifyAccountCreator->connectAccount(
            $this->activeUser->getCompanyId(),
            $accountId,
            [
                'shop' => $shop,
                'token' => $token,
            ]
        );
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
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
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
    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
        return $this;
    }

    /**
     * @return self
     */
    protected function setClientFactory(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return self
     */
    protected function setShopifyAccountCreator(ShopifyAccountCreator $shopifyAccountCreator)
    {
        $this->shopifyAccountCreator = $shopifyAccountCreator;
        return $this;
    }
} 
