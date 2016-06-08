<?php
namespace CG_Shopify\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Shopify\Account as ShopifyAccount;
use CG\Shopify\Client;
use CG\Shopify\Client\Factory as ClientFactory;
use CG\Shopify\Credentials;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use CG_Shopify\Controller\AccountController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use InvalidArgumentException;
use Zend\Session\SessionManager;

class Service
{
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
    /** @var SessionManager $sessionManager */
    protected $sessionManager;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService,
        Cryptor $cryptor,
        UrlHelper $urlHelper,
        ClientFactory $clientFactory,
        SessionManager $sessionManager
    ) {
        $this
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setAccountService($accountService)
            ->setCryptor($cryptor)
            ->setUrlHelper($urlHelper)
            ->setClientFactory($clientFactory)
            ->setSessionManager($sessionManager);
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
        $client = $this->clientFactory->createClientForCredentials(new Credentials($this->parseShop($shop)));
        $redirectUrl = $client->getOauthLink($nounce, Client::getRequiredScopes(), $this->getProcessUrl($accountId));

        $session = $this->sessionManager->getStorage();
        if (!isset($session['shopify'])) {
            $session['shopify'] = [];
        }
        if (!isset($session['shopify']['oauth'])) {
            $session['shopify']['oauth'] = [];
        }
        $session['shopify']['oauth'][$shop] = $nounce;

        return $this->jsonModelFactory->newInstance(['redirectUrl' => $redirectUrl]);
    }

    protected function parseShop($shop)
    {
        if (filter_var($shop, FILTER_VALIDATE_URL)) {
            $shop = parse_url($shop, PHP_URL_HOST);
        }
        if (!preg_match('/\.myshopify\.com$/', $shop)) {
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
    protected function setSessionManager(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }
} 
