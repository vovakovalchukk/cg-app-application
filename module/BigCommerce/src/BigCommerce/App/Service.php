<?php
namespace BigCommerce\App;

use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreationService;

class Service
{
    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var BigCommerceAccountCreationService $accountCreationService */
    protected $accountCreationService;

    public function __construct(
        ActiveUserInterface $activeUser,
        ViewModelFactory $viewModelFactory,
        BigCommerceAccountCreationService $accountCreationService
    ) {
        $this
            ->setActiveUser($activeUser)
            ->setViewModelFactory($viewModelFactory)
            ->setAccountCreationService($accountCreationService);
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

    public function processOauth(array $parameters)
    {
        if (!isset($parameters['code'], $parameters['scope'], $parameters['context'])) {
            throw new \InvalidArgumentException('OAuth request did not include all expected parameters');
        }

        $this->validateScope(
            is_array($parameters['scope']) ? $parameters['scope'] : explode(' ', $parameters['scope'])
        );

        $shopHash = $this->getShopHash($parameters['scope']);
        $accountId = null; // TODO: Get accountId from session for selected shop hash

        $this->accountCreationService->connectAccount(
            $this->activeUser->getCompanyId(),
            $accountId,
            array_merge(['shopHash' => $shopHash], $parameters)
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
            $missingScopes[] = $scope . ($writeAccessRequired ? '' : '_read_only');
        }

        if (!empty($missingScopes)) {
            throw new \RuntimeException(
                'OAuth request does not require all required scopes. Missing scopes: ' . implode(', ', $missingScopes)
            );
        }
    }

    protected function getShopHash($context)
    {
        if (preg_match('|^stores/(?<hash>.+)$|', $context, $store)) {
            return $store['hash'];
        }
        throw new \InvalidArgumentException('OAuth request does not have a valid store context');
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
}
