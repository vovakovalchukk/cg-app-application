<?php
namespace CG_Shopify\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Shopify\Account as ShopifyAccount;
use CG\Shopify\Credentials;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use CG_Shopify\Controller\AccountController;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service
{
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var Cryptor $cryptor */
    protected $cryptor;
    /** @var UrlHelper $urlHelper */
    protected $urlHelper;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        AccountService $accountService,
        Cryptor $cryptor,
        UrlHelper $urlHelper
    ) {
        $this
            ->setViewModelFactory($viewModelFactory)
            ->setAccountService($accountService)
            ->setCryptor($cryptor)
            ->setUrlHelper($urlHelper);
    }

    public function getSetupView($accountId = null, $returnUrl = null)
    {
        $view = $this->viewModelFactory->newInstance(
            [
                'isHeaderBarVisible' => false,
                'isSidebarPresent' => false,
                'accountId' => $accountId,
                'submitUrl' => $this->getSubmitUrl(),
                'returnUrl' => $returnUrl,
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
} 
