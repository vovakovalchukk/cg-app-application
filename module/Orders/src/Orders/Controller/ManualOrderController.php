<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\ManualOrder\Service;
use Orders\Order\Service as OrderService;
use Orders\Module;
use Zend\Mvc\Controller\AbstractActionController;

class ManualOrderController extends AbstractActionController
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var UsageService */
    protected $usageService;
    /** @var Service */
    protected $service;
    /** @var OrderService */
    protected $orderService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        UsageService $usageService,
        Service $service,
        OrderService $orderService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setUsageService($usageService)
            ->setService($service)
            ->setOrderService($orderService);
    }

    public function indexAction()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $currenciesList = $this->service->getCurrencyOptions();

        $carrierList = $this->orderService->getCarriersData();
        $carrierDropdownOptions = [];
        foreach ($carrierList as $carrier) {
            $carrierDropdownOptions[] = [
                'name' => $carrier,
                'value' => $carrier,
            ];
        }

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('currenciesJson', json_encode($currenciesList))
            ->setVariable('carriersJson', json_encode($carrierDropdownOptions))
            ->addChild($this->getSidebar(), 'sidebar');

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