<?php
namespace Settings\Controller;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\User\Entity as User;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Csv\Service as OrderCsvService;
use Settings\Module;
use Zend\View\Model\ViewModel;

class ExportController extends AdvancedController
{
    const ROUTE_EXPORT = 'Export Data';
    const ROUTE_EXPORT_ORDER = 'Export Order Data';
    const ROUTE_EXPORT_ORDER_ITEM = 'Export Order Item Data';

    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var OrderCsvService $orderCsvService */
    protected $orderCsvService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ActiveUserContainer $activeUserContainer,
        OrderService $orderService,
        OrderCsvService $orderCsvService
    ) {
        $this
            ->setViewModelFactory($viewModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderService($orderService)
            ->setOrderCsvService($orderCsvService);
    }

    public function exportAction()
    {
        return $this->newViewModel(
            [
                'route' => implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_EXPORT]),
                'ordersRoute' => static::ROUTE_EXPORT_ORDER,
                'orderItemsRoute' => static::ROUTE_EXPORT_ORDER_ITEM,
                'isHeaderBarVisible' => false,
                'subHeaderHide' => true,
            ]
        );
    }

    public function exportOrderAction()
    {
        $csv = $this->orderCsvService->generateCsvForOrders($this->getOrders());
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportOrderItemAction()
    {
        $csv = $this->orderCsvService->generateCsvForOrdersAndItems($this->getOrders());
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    /**
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }

    protected function getOrders()
    {
        $orderFilter = (new OrderFilter())
            ->setLimit('all')
            ->setOrganisationUnitId($this->getActiveUser()->getOuList());

        return $this->orderService->fetchCollectionByFilter($orderFilter);
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
    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setOrderCsvService(OrderCsvService $orderCsvService)
    {
        $this->orderCsvService = $orderCsvService;
        return $this;
    }
} 
