<?php
namespace Settings\Controller;

use CG\Order\Service\Filter as OrderFilter;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\User\Entity as User;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\Order\Csv\Service as OrderCsvService;
use Settings\Module;
use Zend\View\Model\ViewModel;

class ExportController extends AdvancedController
{
    const ROUTE_EXPORT = 'Export Data';
    const ROUTE_EXPORT_ORDER = 'Export Order Data';
    const ROUTE_EXPORT_ORDER_CHECK = 'Check';
    const ROUTE_EXPORT_ORDER_PROGRESS = 'Progress';
    const ROUTE_EXPORT_ORDER_ITEM = 'Export Order Item Data';
    const ROUTE_EXPORT_ORDER_ITEM_CHECK = 'Check';
    const ROUTE_EXPORT_ORDER_ITEM_PROGRESS = 'Progress';

    const PROGRESS_KEY_NAME = 'orderExportProgressKey';

    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrderCsvService $orderCsvService */
    protected $orderCsvService;
    /** @var UsageService */
    protected $usageService;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ActiveUserContainer $activeUserContainer,
        OrderCsvService $orderCsvService,
        UsageService $usageService,
        JsonModelFactory $jsonModelFactory
    ) {
        $this
            ->setViewModelFactory($viewModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderCsvService($orderCsvService)
            ->setUsageService($usageService)
            ->setJsonModelFactory($jsonModelFactory);
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
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvFromFilterForOrders($this->getOrderFilter(), $guid);
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportOrderItemAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvFromFilterForOrdersAndItems($this->getOrderFilter(), $guid);
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportCheckAction()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $guid = uniqid('', true);
        $this->orderCsvService->startProgress($guid);
        return $this->jsonModelFactory->newInstance(
            ["allowed" => true, "guid" => $guid]
        );
    }

    public function exportProgressAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME);
        $count = $this->orderCsvService->checkToCsvGenerationProgress($guid);
        return $this->jsonModelFactory->newInstance(
            ["progressCount" => $count]
        );
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

    /**
     * @return OrderFilter
     */
    protected function getOrderFilter()
    {
        return (new OrderFilter())
            ->setLimit('all')
            ->setOrganisationUnitId($this->getActiveUser()->getOuList());
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
    protected function setOrderCsvService(OrderCsvService $orderCsvService)
    {
        $this->orderCsvService = $orderCsvService;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }
} 
