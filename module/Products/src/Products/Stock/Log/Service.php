<?php
namespace Products\Stock\Log;

use CG\Channel\Type as ChannelType;
use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Stock\Adjustment as StockAdjustment;
use CG\Stock\Audit\Combined\Collection;
use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Service as StockLogService;
use CG\Stock\Auditor as StockAuditor;
use CG_UI\View\Helper\DateFormat as DateFormatter;
use CG\User\ActiveUserInterface;
use Orders\Module as OrdersModule;
use Products\Module as ProductsModule;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\MvcEvent;

class Service
{
    const DEFAULT_IMAGE_URL = '/noproductsimage.png';

    /** @var ProductService */
    protected $productService;
    /** @var StockLogService */
    protected $stockLogService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var DateFormatter */
    protected $dateFormatter;

    protected $actionMap = [
        StockAuditor::JOB_CREATED_ACTION => 'Channel Update Requested',
    ];

    public function __construct(
        ProductService $productService,
        StockLogService $stockLogService,
        ActiveUserInterface $activeUserContainer,
        DateFormatter $dateFormatter
    ) {
        $this->setProductService($productService)
            ->setStockLogService($stockLogService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDateFormatter($dateFormatter);
    }

    public function getProductDetails($productId)
    {
        $product = $this->productService->fetch($productId);
        $nameProduct = $skuProduct = $imageProduct = $product;

        if ($product->isVariation()) {
            $parentProduct = $this->productService->fetch($product->getParentProductId());
            $nameProduct = $imageProduct = $parentProduct;
        } else if ($product->isParent()) {
            $variations = $product->getVariations();
            $variations->rewind();
            $skuProduct = $variations->current();
        }

        $details = [
            'name' => $nameProduct->getName(),
            'sku' => $skuProduct->getSku(),
            'image' => $this->getProductImageUrl($imageProduct),
        ];

        return $details;
    }

    protected function getProductImageUrl(Product $product)
    {
        if (count($product->getImages()) == 0) {
            return static::DEFAULT_IMAGE_URL;
        }
        $product->getImages()->rewind();
        $image = $product->getImages()->current();
        return $image->getUrl();
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $filter->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
        return $this->stockLogService->fetchCollectionByFilter($filter);
    }

    public function stockLogsToUiData(Collection $stockLogs, MvcEvent $event)
    {
        $data = $stockLogs->toArray();
        $this->addAccountDetailsToUiData($data, $event)
            ->addOrderDetailsToUiData($data, $event)
            ->addListingDetailsToUiData($data, $event)
            ->addStatusDetailsToUiData($data)
            ->addStockManagementDetailsToUiData($data)
            ->addDateTimeDetailsToUiData($data)
            ->addAvailableQtyDetailsToUiData($data)
            ->addAdjustmentDetailsToUiData($data)
            ->formatIdForUiData($data)
            ->formatItidForUiData($data)
            ->formatActionForUiData($data);
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

    protected function addListingDetailsToUiData(array &$data, MvcEvent $event)
    {
        foreach ($data as &$row) {
            $row['listingLink'] = '';
            if (!isset($row['listingId']) || (int)$row['listingId'] == 0) {
                $row['listingId'] = '';
                continue;
            }
            $row['listingLink'] = $event->getRouter()->assemble(
                [],
                ['name' => ProductsModule::ROUTE]
            ) . '?' . http_build_query(['search' => $row['sku']]);
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
            if (isset($row['stockManagement'])) {
                $row['stockManagement'] = ($row['stockManagement'] ? 'On' : 'Off');
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
            if (!isset($row['adjustmentQty']) || (int)$row['adjustmentQty'] == 0 ) {
                continue;
            }
            $quantity = $row['adjustmentQty'];
            $sign = ($row['adjustmentOperator'] == StockAdjustment::OPERATOR_DEC ? '-' : '+');
            $type = $row['adjustmentType'];
            $row[$type . 'Qty'] = $sign . $quantity;
        }
        return $this;
    }

    protected function formatIdForUiData(array &$data)
    {
        foreach ($data as &$row) {
            $row['id'] = preg_replace('/-/', '-<br />', $row['id'], 1);
        }
        return $this;
    }

    protected function formatItidForUiData(array &$data)
    {
        foreach ($data as &$row) {
            $row['itid'] = preg_replace('/-/', '-<br />', $row['itid']);
        }
        return $this;
    }

    protected function formatActionForUiData(array &$data)
    {
        foreach ($data as &$row) {
            if (!isset($row['action']) || !isset($this->actionMap[$row['action']])) {
                continue;
            }
            $row['action'] = $this->actionMap[$row['action']];
        }
        return $this;
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

    protected function setDateFormatter(DateFormatter $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
        return $this;
    }
}
