<?php
namespace Settings\Controller;

use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Product\Client\Service as ProductService;
use CG\User\Entity as User;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\Order\Csv\Service as OrderCsvService;
use Products\Product\Csv\Exporter as ProductCsvService;
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
    const ROUTE_EXPORT_PRODUCT = 'Export Product Data';

    const PROGRESS_KEY_NAME = 'orderExportProgressKey';

    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var UserOUService $userOUService */
    protected $userOUService;
    /** @var OrderCsvService $orderCsvService */
    protected $orderCsvService;
    /** @var UsageService */
    protected $usageService;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ProductCsvService */
    protected $productCsvService;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOUService $userOUService,
        OrderCsvService $orderCsvService,
        UsageService $usageService,
        JsonModelFactory $jsonModelFactory,
        ProductCsvService $productCsvService,
        FeatureFlagsService $featureFlagsService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->userOUService = $userOUService;
        $this->orderCsvService = $orderCsvService;
        $this->usageService = $usageService;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->productCsvService = $productCsvService;
        $this->featureFlagsService = $featureFlagsService;
    }

    public function exportAction()
    {
        $showProductExport = $this->featureFlagsService->isActive(
            ProductService::FEATURE_FLAG_PRODUCT_EXPORT,
            $this->userOUService->getRootOuByActiveUser()
        );
        $view = $this->newViewModel(
            [
                'route' => implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_EXPORT]),
                'ordersRoute' => static::ROUTE_EXPORT_ORDER,
                'orderItemsRoute' => static::ROUTE_EXPORT_ORDER_ITEM,
                'productsRoute' => static::ROUTE_EXPORT_PRODUCT,
                'showProductExport' => $showProductExport,
                'isHeaderBarVisible' => false,
                'subHeaderHide' => true,
            ]
        );
        $view->addChild($this->getChannelSelectForProductExport(), 'channelSelectForProductExport');
        return $view;
    }

    protected function getChannelSelectForProductExport(): ViewModel
    {
        $select = $this->newViewModel([
            'id' => 'export-products-channel-select',
            'name' => 'channel',
            'options' => [
                // Hard-coded to the supported channels for now.
                // Once all are supported get these from Filters\Options\Channel::getSelectOptions()
                ['title' => 'Amazon', 'value' => 'amazon', 'selected' => true]
            ]
        ]);
        $select->setTemplate('elements/custom-select.mustache');
        return $select;
    }

    public function exportOrderAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvForAllOrders(
            $this->getActiveUser()->getOuList(),
            $guid
        );
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportOrderItemAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvForAllOrdersAndItems(
            $this->getActiveUser()->getOuList(),
            $guid
        );
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportProductAction()
    {
        return new FileResponse(
            ProductCsvService::MIME_TYPE,
            $this->productCsvService->getFileName($this->params()->fromRoute('channel')),
            (string) $this->productCsvService->exportToCsv(
                $this->params()->fromRoute('channel')
            )
        );
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
        return $this->userOUService->getActiveUser();
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
} 
