<?php
namespace Settings\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Product\Client\Service as ProductService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\Entity as User;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\Order\Csv\Fields\Orders as OrdersFields;
use Orders\Order\Csv\Fields\OrdersItems as OrdersItemsFields;
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
    /** @var AccountService */
    protected $accountService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOUService $userOUService,
        OrderCsvService $orderCsvService,
        UsageService $usageService,
        JsonModelFactory $jsonModelFactory,
        ProductCsvService $productCsvService,
        FeatureFlagsService $featureFlagsService,
        AccountService $accountService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->userOUService = $userOUService;
        $this->orderCsvService = $orderCsvService;
        $this->usageService = $usageService;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->productCsvService = $productCsvService;
        $this->featureFlagsService = $featureFlagsService;
        $this->accountService = $accountService;
    }

    public function exportAction()
    {
        $view = $this->newViewModel(
            [
                'route' => implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_EXPORT]),
                'ordersRoute' => static::ROUTE_EXPORT_ORDER,
                'orderItemsRoute' => static::ROUTE_EXPORT_ORDER_ITEM,
                'productsRoute' => static::ROUTE_EXPORT_PRODUCT,
                'showProductExport' => $this->shouldShowProductExport(),
                'isHeaderBarVisible' => false,
                'subHeaderHide' => true,
            ]
        );
        $view->addChild($this->getChannelSelectForProductExport(), 'channelSelectForProductExport');
        return $view;
    }

    protected function shouldShowProductExport(): bool
    {
        $featureEnabled = $this->featureFlagsService->isActive(
            ProductService::FEATURE_FLAG_PRODUCT_EXPORT,
            $this->userOUService->getRootOuByActiveUser()
        );
        if (!$featureEnabled) {
            return false;
        }
        return $this->hasAccountsForProductExport();
    }

    protected function hasAccountsForProductExport(): bool
    {
        // Only certain channels are supported for now.
        // Once all channels are supported this can just return true or be removed completely
        $channelOptions = $this->getChannelSelectOptionsForProductExport();
        $channels = array_column($channelOptions, 'value');
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setChannel($channels)
            ->setOrganisationUnitId($this->userOUService->getActiveUser()->getOuList());
        try {
            $accounts = $this->accountService->fetchByFilter($filter);
            return true;
        } catch (NotFound $e) {
            return false;
        }
    }

    protected function getChannelSelectOptionsForProductExport(): array
    {
        // Hard-coded to the supported channels for now.
        // Once all are supported get these from Filters\Options\Channel::getSelectOptions()
        return [
            ['title' => 'Amazon', 'value' => 'amazon', 'selected' => true]
        ];
    }

    protected function getChannelSelectForProductExport(): ViewModel
    {
        $select = $this->newViewModel([
            'id' => 'export-products-channel-select',
            'name' => 'channel',
            'options' => $this->getChannelSelectOptionsForProductExport()
        ]);
        $select->setTemplate('elements/custom-select.mustache');
        return $select;
    }

    public function exportOrderAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvForAllOrders(
            $this->getActiveUser()->getOuList(),
            $guid,
            OrdersFields::getFields()
        );
        return new FileResponse(OrderCsvService::MIME_TYPE, OrderCsvService::FILENAME, (string) $csv);
    }

    public function exportOrderItemAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME, null);
        $csv = $this->orderCsvService->generateCsvForAllOrdersAndItems(
            $this->getActiveUser()->getOuList(),
            $guid,
            OrdersItemsFields::getFields()
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
