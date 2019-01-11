<?php
namespace Products\Stock\Log;

use CG\Channel\Type as ChannelType;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Audit\Combined\Collection;
use CG\Stock\Audit\Combined\Entity;
use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Service as StockLogService;
use CG\Stock\Auditor as StockAuditor;
use CG_UI\View\DataTable;
use CG_UI\View\Filters as UIFilters;
use CG_UI\View\Helper\DateFormat as DateFormatter;
use CG\User\ActiveUserInterface;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\UserPreference\Shared\Entity as UserPreference;
use Orders\Module as OrdersModule;
use Products\Module as ProductsModule;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\MvcEvent;

class Service
{
    const DEFAULT_IMAGE_URL = 'img/noproductsimage.png';
    const COL_PREF_KEY = 'stock-log-columns';

    /** @var ProductService */
    protected $productService;
    /** @var StockLogService */
    protected $stockLogService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var UserPreferenceService */
    protected $userPreferenceService;
    /** @var DateFormatter */
    protected $dateFormatter;
    /** @var UserPreference */
    protected $activeUserPreference;

    protected $actionMap = [
        StockAuditor::JOB_CREATED_ACTION => 'Channel Update Requested',
    ];
    protected $actionsWithNoStockManagementData = [
        StockAuditor::SOURCE_USER => true,
        StockAuditor::SOURCE_API => true,
        StockAuditor::PRODUCT_CREATE_ACTION => true,
    ];
    protected $filterOptionsMethods = [
        'sku' => 'getSkuFilterOptions',
    ];
    protected $defaultColumns = [
        'stid' => false,
        'productId' => false,
        'stockId' => false,
        'locationId' => false,
    ];

    public function __construct(
        ProductService $productService,
        StockLogService $stockLogService,
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService,
        DateFormatter $dateFormatter
    ) {
        $this->setProductService($productService)
            ->setStockLogService($stockLogService)
            ->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService)
            ->setDateFormatter($dateFormatter);
    }

    public function getProductDetails($productId)
    {
        $product = $this->productService->fetch($productId);
        $nameProduct = $skuProduct = $imageProduct = $product;
        $skuOptions = [[
            'value' => $skuProduct->getSku(),
            'title' => $skuProduct->getSku(),
            'selected' => true,
        ]];

        if ($product->isVariation()) {
            $parentProduct = $this->productService->fetch($product->getParentProductId());
            $nameProduct = $imageProduct = $parentProduct;
            $skuOptions = $this->getSkuOptionsFromVariations($parentProduct->getVariations(), $product->getSku());
        } else if ($product->isParent()) {
            $variations = $product->getVariations();
            $variations->rewind();
            $skuProduct = $variations->current();
            $skuOptions = $this->getSkuOptionsFromVariations($variations, $skuProduct->getSku());
        }

        $details = [
            'name' => $nameProduct->getName(),
            'sku' => $skuProduct->getSku(),
            'skuOptions' => $skuOptions,
            'image' => $this->getProductImageUrl($imageProduct),
        ];

        return $details;
    }

    protected function getSkuOptionsFromVariations(ProductCollection $variations, $selectedSku)
    {
        $skuOptions = [];
        foreach ($variations as $variation) {
            $skuOptions[] = [
                'value' => $variation->getSku(),
                'title' => $variation->getSku(),
                'selected' => ($variation->getSku() == $selectedSku),
            ];
        }
        return $skuOptions;
    }

    protected function getProductImageUrl(Product $product)
    {
        if (count($product->getImages()) == 0) {
            return ProductsModule::PUBLIC_FOLDER . static::DEFAULT_IMAGE_URL;
        }
        $product->getImages()->rewind();
        $image = $product->getImages()->current();
        return $image->getUrl();
    }

    public function setUiFilterOptions(UIFilters $filters, array $productDetails)
    {
        foreach ($filters->getFilterRows() as $filterRow) {
            foreach ($filterRow as &$filter) {
                $name = $filter->getVariable('name');
                if (!isset($this->filterOptionsMethods[$name])) {
                    continue;
                }
                $filter->setVariable('options', call_user_func([$this, $this->filterOptionsMethods[$name]], $productDetails));
            }
        }
    }

    protected function getSkuFilterOptions(array $productDetails)
    {
        return $productDetails['skuOptions'];
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $filter->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
        try {
            return $this->stockLogService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new Collection(Entity::class, __FUNCTION__, $filter->toArray());
        }
    }

    public function stockLogsToUiData(Collection $stockLogs, MvcEvent $event, Filter $filter)
    {
        $data = $stockLogs->toArray();
        $this->addAccountDetailsToUiData($data, $event)
            ->addOrderDetailsToUiData($data, $event)
            ->addProductDetailsToUiData($data, $event)
            ->addListingDetailsToUiData($data, $event)
            ->addStatusDetailsToUiData($data)
            ->addStockManagementDetailsToUiData($data)
            ->addDateTimeDetailsToUiData($data)
            ->addAvailableQtyDetailsToUiData($data)
            ->addAdjustmentDetailsToUiData($data)
            ->formatActionForUiData($data, $filter);
        return $data;
    }

    protected function addAccountDetailsToUiData(array &$data, MvcEvent $event)
    {
        foreach ($data as &$row) {
            $row['accountLink'] = '';
            if (isset($row['accountId'])) {
                $row['accountLink'] = $event->getRouter()->assemble(
                    ['account' => $row['accountId'], 'type' => ChannelType::SALES],
                    ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ROUTE_ACCOUNT]
                );
            }
        }
        return $this;
    }

    protected function addOrderDetailsToUiData(array &$data, MvcEvent $event)
    {
        foreach ($data as &$row) {
            $row['orderLink'] = '';
            if (isset($row['orderId'])) {
                $row['orderLink'] = $event->getRouter()->assemble(
                    ['order' => $row['orderId']],
                    ['name' => OrdersModule::ROUTE . '/order']
                );
            }
        }
        return $this;
    }

    protected function addProductDetailsToUiData(array &$data, MvcEvent $event)
    {
        foreach ($data as &$row) {
            $link = $event->getRouter()->assemble(
                [],
                ['name' => ProductsModule::ROUTE]
            );
            $row['productLink'] = $link . '?' . http_build_query(['search' => $row['sku']]);
            if (isset($row['referenceSku'])) {
                $row['referenceProductLink'] = $link . '?' . http_build_query(['search' => $row['referenceSku']]);
            }
        }
        return $this;
    }

    protected function addListingDetailsToUiData(array &$data, MvcEvent $event)
    {
        foreach ($data as &$row) {
            if (isset($row['listingId']) && (int)$row['listingId'] > 0) {
                continue;
            }
            $row['listingId'] = '';
        }
        return $this;
    }

    protected function addStatusDetailsToUiData(array &$data)
    {
        foreach ($data as &$row) {
            $row['status'] = '';
            if (isset($row['listingStatus']) && $row['listingStatus'] != '') {
                $row['status'] = $row['listingStatus'];
            }
            if (isset($row['itemStatus']) && $row['itemStatus'] != '') {
                $row['status'] = $row['itemStatus'];
            }
            $row['statusClass'] = str_replace(' ', '-', $row['status']);
        }
        return $this;
    }

    protected function addStockManagementDetailsToUiData(array &$data)
    {
        foreach ($data as &$row) {
            if (isset($row['stockManagement']) && !isset($this->actionsWithNoStockManagementData[$row['action']])) {
                $row['stockManagement'] = ($row['stockManagement'] ? 'ON' : 'OFF');
                $row['stockManagementClass'] = strtolower($row['stockManagement']);
            } else {
                $row['stockManagement'] = '';
                $row['stockManagementClass'] = '';
            }
        }

        return $this;
    }

    protected function addDateTimeDetailsToUiData(array &$data)
    {
        $dateFormatter = $this->dateFormatter;
        foreach ($data as &$row) {
            $row['dateTime'] = $dateFormatter($row['date'].' '.$row['time']);
            $row['dateTime'] = str_replace(' ', '<br />', $row['dateTime']);
        }
        return $this;
    }

    protected function addAvailableQtyDetailsToUiData(array &$data)
    {
        foreach ($data as &$row) {
            if (!isset($row['onHandQty'])) {
                continue;
            }
            $row['availableQty'] = (int)$row['onHandQty'] - (int)$row['allocatedQty'];
        }
        return $this;
    }

    protected function addAdjustmentDetailsToUiData(array &$data)
    {
        foreach ($data as &$row) {
            $this->formatAdjustmentDetailsForUiData($row, 'adjustmentQty');
            $this->formatAdjustmentDetailsForUiData($row, 'adjustmentReferenceQuantity', 'Reference');
        }
        return $this;
    }

    protected function formatAdjustmentDetailsForUiData(array &$row, string $field, string $prefix = null)
    {
        if (!isset($row[$field]) || (int)$row[$field] == 0 ) {
            return;
        }
        $quantity = $row[$field];
        $sign = ($row['adjustmentOperator'] == StockAdjustment::OPERATOR_DEC ? '-' : '+');
        $type = $row['adjustmentType'];
        $row[$type . ($prefix ?? '') . 'Qty'] = $sign . $quantity;
    }

    protected function formatActionForUiData(array &$data, Filter $filter)
    {
        foreach ($data as &$row) {
            if (!isset($row['action'])) {
                continue;
            }
            if ($row['action'] == 'Stock Log' && count($filter->getType()) != 1) {
                $row['DT_RowClass'] = 'stock-log-row';
            }
            if (!isset($this->actionMap[$row['action']])) {
                continue;
            }
            $row['action'] = $this->actionMap[$row['action']];
        }
        return $this;
    }

    public function configureDataTableColumns(DataTable $dataTable)
    {
        $columns = $dataTable->getColumns();
        $associativeColumns = [];
        foreach ($columns as $column) {
            $associativeColumns[$column->getColumn()] = $column;
        }

        $columnPrefs = $this->getColumnPreferencesForActiveUser();
        foreach ($columnPrefs as $name => $on) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setVisible(
                filter_var($on, FILTER_VALIDATE_BOOLEAN)
            );
        }
    }

    protected function getColumnPreferencesForActiveUser()
    {
        $userPrefColumns = $this->fetchUserPrefItem(static::COL_PREF_KEY);
        if (!empty($userPrefColumns)) {
            return $userPrefColumns;
        }
        if ($this->activeUserContainer->isAdmin()) {
            return [];
        }
        return $this->defaultColumns;
    }

    public function updateUserPrefStockLogColumns(array $updatedColumns)
    {
        if ($this->activeUserContainer->isAdmin()) {
            return;
        }
        $storedColumns = $this->fetchUserPrefItem(static::COL_PREF_KEY);
        foreach ($updatedColumns as $name => $on) {
            $storedColumns[$name] = $on;
        }

        $this->saveUserPrefItem(static::COL_PREF_KEY, $storedColumns);

        return $this;
    }

    protected function fetchUserPrefItem($key)
    {
        $userPrefsPref = $this->getActiveUserPreference()->getPreference();
        $storedItem = (isset($userPrefsPref[$key]) ? $userPrefsPref[$key] : []);
        return $storedItem;
    }

    protected function saveUserPrefItem($key, $value)
    {
        $userPrefs = $this->getActiveUserPreference();
        $userPrefsPref = $userPrefs->getPreference();
        $userPrefsPref[$key] = $value;
        $userPrefs->setPreference($userPrefsPref);

        $this->userPreferenceService->save($userPrefs);
    }

    public function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->activeUserContainer->getActiveUser()->getId();
            $this->activeUserPreference = $this->userPreferenceService->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function setStockLogService(StockLogService $stockLogService)
    {
        $this->stockLogService = $stockLogService;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    protected function setDateFormatter(DateFormatter $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
        return $this;
    }
}
