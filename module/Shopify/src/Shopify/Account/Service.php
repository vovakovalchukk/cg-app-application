<?php
namespace Shopify\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Creation\SetupViewInterface;
use CG\Shopify\Account as ShopifyAccount;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG\Shopify\Client;
use CG\Shopify\Client\Factory as ClientFactory;
use CG\Shopify\Credentials;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use InvalidArgumentException;
use Shopify\Controller\AccountController;
use Zend\Session\Container as Session;

class Service implements LoggerAwareInterface, SetupViewInterface
{
    use LogTrait;

    const LOG_CODE_DUPLICATE_OAUTH_RESPONSE = 'Duplicate OAuth Response';
    const LOG_MSG_DUPLICATE_OAUTH_RESPONSE = 'We have already processed this OAuth response';
    const LOG_CODE_INVALID_OAUTH_RESPONSE = 'Invalid Shopify OAuth Response';
    const LOG_MSG_INVALID_OAUTH_RESPONSE = 'Expected Fields';
    const LOG_CODE_INVALID_NONCE = 'Invalid Shopify OAuth Nonce';
    const LOG_MSG_INVALID_NONCE = 'Nonce Mismatch';
    const LOG_CODE_PROCESSED_OAUTH_RESPONSE = 'Processed OAuth Response';
    const LOG_MSG_PROCESSED_OAUTH_RESPONSE = 'OAuth reponse has been processed and linked to an account';

    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var AccountService $accountService */
    protected $accountService;
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
            $accountData = $account->toArray();
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

    public function getLink(string $shopHost, ?int $accountId = null): string
    {
        $shopHost = $this->parseShopHost(strtolower($shopHost));
        $client = $this->clientFactory->createClientForShop($shopHost);
        $redirectUrl = $client->getOauthLink($nonce, Client::getRequiredScopes(), $this->getProcessUrl($accountId));

        if (!isset($this->session['oauth']) || !is_array($this->session['oauth'])) {
            $this->session['oauth'] = [];
        }

        $this->session['oauth'][$shopHost] = ['accountId' => $accountId, 'nonce' => $nonce];
        $this->logDebugDump($this->session->getArrayCopy(), 'Session contents on redirection to Shopify', [], 'ShopifyAccountConnection::OutboundSession');

        return $redirectUrl;
    }

    public function getLinkJson(string $shopHost, ?int $accountId = null)
    {
        $redirectUrl = $this->getLink($shopHost, $accountId);
        return $this->jsonModelFactory->newInstance(['redirectUrl' => $redirectUrl]);
    }

    protected function parseShopHost($shopHost)
    {
        if (filter_var($shopHost, FILTER_VALIDATE_URL)) {
            $shopHost = parse_url($shopHost, PHP_URL_HOST);
        }
        if (strrpos($shopHost, '.') === false) {
            $shopHost .= '.myshopify.com';
        }
        if (!preg_match('/[a-z0-9\.\-]\.myshopify\.com$/i', $shopHost)) {
            throw new InvalidArgumentException(sprintf('Shop Host (%s) is not a valid Shopify Shop', $shopHost));
        }
        return $shopHost;
    }

    protected function getProcessUrl($accountId = null)
    {
        $route = [ShopifyAccount::ROUTE_SHOPIFY, ShopifyAccount::ROUTE_SETUP, AccountController::ROUTE_SETUP_RETURN];
        return $this->urlHelper->fromRoute(implode('/', $route), [], ['force_canonical' => true]);
    }

    /**
     * @return Account
     */
    public function activateAccount(array $parameters)
    {
        if (!isset($parameters['shop'])) {
            throw new InvalidArgumentException('OAuth response does not include shop');
        }

        $shop = $parameters['shop'];
        $this->logDebugDump($this->session->getArrayCopy(), 'Session contents on redirection from Shopify', [], 'ShopifyAccountConnection::InboundSession');
        if (!isset($this->session['oauth'][$shop]) || !is_array($this->session['oauth'][$shop])) {
            throw new InvalidArgumentException(sprintf('OAuth response for unknown shop \"%s\"', $shop));
        }

        $oAuthSession = array_merge(
            ['accountId' => null, 'nonce' => null, 'processed' => false],
            $this->session['oauth'][$shop]
        );

        if ($oAuthSession['accountId'] && $oAuthSession['processed']) {
            $this->logPrettyInfo(static::LOG_MSG_DUPLICATE_OAUTH_RESPONSE, ['shop' => $shop, 'accountId' => $oAuthSession['accountId']], [], static::LOG_CODE_DUPLICATE_OAUTH_RESPONSE);
            return $this->accountService->fetch($oAuthSession['accountId']);
        }

        if (!isset($parameters['state'], $parameters['code'])) {
            $this->logPrettyError(static::LOG_MSG_INVALID_OAUTH_RESPONSE, ['shop' => $shop, 'state' => isset($parameters['state']) ? $parameters['state'] : '-', 'code' => isset($parameters['code']) ? $parameters['code'] : '-'], [], static::LOG_CODE_INVALID_OAUTH_RESPONSE);
            throw new InvalidArgumentException('Invalid OAuth response from Shopify');
        }

        $nonce = $parameters['state'];
        $code = $parameters['code'];

        if ($oAuthSession['nonce'] != $nonce) {
            $this->logPrettyError(static::LOG_MSG_INVALID_NONCE, ['Session' => $oAuthSession['nonce'] ?: '-', 'OAuth' => $nonce ?: '-'], [], static::LOG_CODE_INVALID_NONCE);
            throw new InvalidArgumentException(
                sprintf("OAuth response has been comprimised:\n\"%s\" != \"%s\"", $oAuthSession['nonce'] ?: '-', $nonce ?: '-')
            );
        }

        $client = $this->clientFactory->createClientForShop($shop);
        $client->hmacSignatureValidation($parameters);
        $token = $client->getToken($code, Client::getRequiredScopes());

        $account = $this->shopifyAccountCreator->connectAccount(
            $this->activeUser->getCompanyId(),
            $oAuthSession['accountId'],
            [
                'shop' => $shop,
                'token' => $token,
            ]
        );

        $this->logPrettyDebug(static::LOG_MSG_PROCESSED_OAUTH_RESPONSE, ['shop' => $shop, 'accountId' => $account->getId()], [], static::LOG_CODE_PROCESSED_OAUTH_RESPONSE);
        $oAuthSession['accountId'] = $account->getId();
        $oAuthSession['processed'] = true;
        $this->session['oauth'][$shop] = $oAuthSession;
        return $account;
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
