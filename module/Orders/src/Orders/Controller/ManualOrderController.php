<?php
namespace Orders\Controller;

use CG\Order\Shared\Entity as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OuService;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\ManualOrder\Service;
use Orders\Module;
use Orders\Order\Service as OrderService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ManualOrderController extends AbstractActionController
{
    const ROUTE_INDEX_URL = '/new';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Service */
    protected $service;
    /** @var OuService */
    protected $ouService;
    /** @var OrderService */
    protected $orderService;
    /** @var ActiveUserContainer */
    protected $activeUserContainer;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        Service $service,
        OuService $ouService,
        ActiveUserContainer $activeUserContainer,
        OrderService $orderService,
        AccessUsageExceededService $accessUsageExceededService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setOuService($ouService)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderService($orderService);
        $this->accessUsageExceededService = $accessUsageExceededService;
    }

    public function indexAction()
    {
        return $this->buildResponse();
    }

    public function duplicateExistingOrderAction()
    {
        $orderId = $this->params()->fromRoute('order');
        $order = $this->orderService->getOrder($orderId);
        return $this->buildResponse($order);
    }

    protected function buildResponse(?OrderEntity $order = null): ViewModel
    {
        $this->accessUsageExceededService->checkUsage();
        $currenciesList = $this->service->getCurrencyOptions($order);
        $tradingCompanies = $this->getTradingCompanyOptions($order);
        $carrierDropdownOptions = $this->getCarrierDropdownOptions();

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/manual-order/index')
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('currenciesJson', json_encode($currenciesList))
            ->setVariable('carriersJson', json_encode($carrierDropdownOptions))
            ->setVariable('tradingCompanies', json_encode($tradingCompanies))
            ->addChild($this->getBuyerMessage(), 'buyerMessage')
            ->addChild($this->getAddressInformation($order), 'addressInformation')
            ->addChild($this->getOrderAlert(), 'orderAlert')
            ->addChild($this->getSidebar(), 'sidebar');

        return $view;
    }

    protected function getTradingCompanyOptions(?OrderEntity $order = null): array
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        /** @var OrganisationUnit $rootOu */
        $rootOu = $this->ouService->fetch($rootOuId);
        $tradingCompanyOptions = [[
            'name' => $rootOu->getAddressCompanyName(),
            'value' => $rootOuId,
            'selected' => $order ? $order->getOrganisationUnitId() == $rootOuId : false
        ]];

        try {
            $tradingCompanies = $this->ouService->fetchFiltered('all', 1, $rootOuId);
        } catch (\Exception $e) {
            return $tradingCompanyOptions;
        }

        /** @var OrganisationUnit $ou */
        foreach ($tradingCompanies as $ou) {
            $tradingCompanyOptions[] = [
                'name' => $ou->getAddressCompanyName(),
                'value' => $ou->getId(),
                'selected' =>  $order ? $order->getOrganisationUnitId() == $ou->getId() : false
            ];;
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

    protected function getAddressInformation(?OrderEntity $order = null)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/addressInformation');
        $view->setVariable('order', $order);
        $view->setVariable('addressSaveUrl', 'Orders/new/create');
        $view->setVariable('billingAddressEditable', true);
        $view->setVariable('shippingAddressEditable', true);
        $view->setVariable('requiresSaveButton', false);
        $view->setVariable('includeAddressCopy', false);
        $view->setVariable('includeUseBillingInfo', $order === null);
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

        } catch (\BadFunctionCallException $e) {
            $view->setVariable('success', false)
                ->setVariable('message', $e->getMessage());
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