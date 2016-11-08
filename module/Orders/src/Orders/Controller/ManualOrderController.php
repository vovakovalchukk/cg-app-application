<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\ManualOrder\Service;
use Orders\Order\Service as OrderService;
use CG\OrganisationUnit\Service as OuService;
use CG\Order\Shared\Entity as OrderEntity;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use Orders\Module;
use Zend\Mvc\Controller\AbstractActionController;

class ManualOrderController extends AbstractActionController
{
    const ROUTE_INDEX_URL = '/new';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var UsageService */
    protected $usageService;
    /** @var Service */
    protected $service;
    /** @var OuService */
    protected $ouService;
    /** @var OrderService */
    protected $orderService;
    /** @var ActiveUserContainer */
    protected $activeUserContainer;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        UsageService $usageService,
        Service $service,
        OuService $ouService,
        ActiveUserContainer $activeUserContainer,
        OrderService $orderService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setUsageService($usageService)
            ->setService($service)
            ->setOuService($ouService)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderService($orderService);
    }

    public function indexAction()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $currenciesList = $this->service->getCurrencyOptions();
        $tradingCompanies = $this->getTradingCompanyOptions();
        $carrierDropdownOptions = $this->getCarrierDropdownOptions();

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('currenciesJson', json_encode($currenciesList))
            ->setVariable('carriersJson', json_encode($carrierDropdownOptions))
            ->setVariable('tradingCompanies', json_encode($tradingCompanies))
            ->addChild($this->getBuyerMessage(), 'buyerMessage')
            ->addChild($this->getAddressInformation(), 'addressInformation')
            ->addChild($this->getOrderAlert(), 'orderAlert')
            ->addChild($this->getSidebar(), 'sidebar');

        return $view;
    }

    protected function getTradingCompanyOptions()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->ouService->fetch($rootOuId);
        $tradingCompanyOptions = [[
            'name' => $rootOu->getAddressCompanyName(),
            'value' => $rootOuId
        ]];

        try {
            $tradingCompanies = $this->ouService->fetchFiltered('all', 1, $rootOuId);
        } catch (\Exception $e) {
            return $tradingCompanyOptions;
        }

        $noneSelected = true;
        foreach ($tradingCompanies as $ou) {
            $option = [
                'name' => $ou->getAddressCompanyName(),
                'value' => $ou->getId()
            ];

            if ($noneSelected) {
                $option['selected'] = true;
                $noneSelected = false;
            }

            $tradingCompanyOptions[] = $option;
        }
        return $tradingCompanyOptions;
    }

    protected function getCarrierDropdownOptions()
    {
        $carrierList = $this->service->getShippingMethods();

        $carrierDropdownOptions = [];
        $carrierDropdownOptions[] = ['name' => 'N/A', 'value' => -1];
        foreach ($carrierList as $carrier) {
            $carrierDropdownOptions[] = [
                'name' => $carrier->getMethod(),
                'value' => $carrier->getId(),
            ];
        }
        return $carrierDropdownOptions;
    }

    protected function getBuyerMessage()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/buyerMessage');
        return $view;
    }

    protected function getAddressInformation()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/addressInformation');
        $view->setVariable('order', null);
        $view->setVariable('addressSaveUrl', 'Orders/new/create');
        $view->setVariable('editable', true);
        $view->setVariable('requiresSaveButton', false);
        $view->setVariable('includeAddressCopy', false);
        $view->setVariable('includeUseBillingInfo', true);
        return $view;
    }

    protected function getOrderAlert()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/orderAlert');
        return $view;
    }

    protected function getSidebar()
    {
        $sidebar = $this->viewModelFactory->newInstance();
        $sidebar->setTemplate('orders/manual-order/sidebar/navbar');

        $links = [
            'product-information' => 'Product Information',
            'order-buyer-message' => 'Buyer Message',
            'address-information' => 'Address Information',
            'order-alert' => 'Order Alert',
            'order-notes' => 'Notes'

        ];
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    public function createAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        try {
            $order = $this->service->createOrderFromPostData($this->params()->fromPost());
            $view->setVariable('success', true)
                ->setVariable('url', $this->url()->fromRoute(Module::ROUTE . '/order', ['order' => $order->getId()]));

        } catch (\Exception $e) {
            $view->setVariable('success', false)
                ->setVariable('message', 'There was a problem creating the order');
        }
        return $view;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    protected function setOuService(OuService $ouService)
    {
        $this->ouService = $ouService;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}