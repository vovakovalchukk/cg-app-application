<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Dataplug\Client as DataplugClient;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use RuntimeException;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;

    const PDF_LABEL_DIR = '/tmp/dataplug-labels';

    const LOG_CODE = 'OrderCourierLabelService';
    const LOG_PDF_MERGE = 'Merging multiple label PDFs into one';
    const LOG_PDF_MERGE_WRITE_FAIL = 'Error writing PDF data to file';
    const LOG_PDF_MERGE_FAIL = 'Error merging PDF data';

    /** @var Mapper */
    protected $mapper;
    /** @var UserOUService */
    protected $userOUService;
    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var DataplugClient */
    protected $dataplugClient;
    /** @var OrderLabelMapper */
    protected $orderLabelMapper;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var OrderTrackingMapper */
    protected $orderTrackingMapper;
    /** @var OrderTrackingService */
    protected $orderTrackingService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;
    /** @var ProductDetailService */
    protected $productDetailService;

    public function __construct(
        Mapper $mapper,
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        DataplugClient $dataplugClient,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailService $productDetailService
    ) {
        $this->setMapper($mapper)
            ->setUserOUService($userOuService)
            ->setOrderService($orderService)
            ->setAccountService($accountService)
            ->setDataplugClient($dataplugClient)
            ->setOrderLabelMapper($orderLabelMapper)
            ->setOrderLabelService($orderLabelService)
            ->setOrderTrackingMapper($orderTrackingMapper)
            ->setOrderTrackingService($orderTrackingService)
            ->setProductDetailMapper($productDetailMapper)
            ->setProductDetailService($productDetailService);
    }

    protected function getOrdersByIds(array $orderIds)
    {
        $filter = (new OrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchCollectionByFilter($filter);
    }

    protected function getOrderLabelForOrder(Order $order)
    {
        $orders = new OrderCollection(Order::class, __FUNCTION__);
        $orders->attach($order);
        $orderLabels = $this->getOrderLabelsForOrders($orders);
        $orderLabels->rewind();
        return $orderLabels->current();
    }

    protected function getOrderLabelsForOrders(OrderCollection $orders)
    {
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $orderIds = $orders->getArrayOf('id');
        $filter = (new OrderLabelFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderId($orderIds)
            ->setStatus($labelStatusesNotCancelled);
        return $this->orderLabelService->fetchCollectionByFilter($filter);
    }

    protected function mergePdfData(array $pdfsData)
    {
        if (count($pdfsData) == 1) {
            return $pdfsData[0];
        }
        $this->logDebug(static::LOG_PDF_MERGE, [], static::LOG_CODE);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $fileDir = static::PDF_LABEL_DIR . '/' . $rootOu->getId();
        $this->ensureDir($fileDir);
        $fileNames = [];
        foreach ($pdfsData as $pdfData) {
            $fileName = $fileDir . '/' . microtime(true) . '.pdf';
            $result = file_put_contents($fileName, $pdfData);
            if (!$result) {
                throw new RuntimeException(static::LOG_PDF_MERGE_WRITE_FAIL);
            }
            $fileNames[] = $fileName;
        }

        $outputFileName = $fileDir . '/merged-' . microtime(true) . '.pdf';
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $outputFileName . ' ' . implode(' ', $fileNames);
        $output = null;
        $retVal = null;
        exec($cmd, $output, $retVal);
        if ($retVal > 0) {
            $this->logDebugDump($output, 'PDF merge failed, output follows', [], static::LOG_CODE);
            throw new RuntimeException(static::LOG_PDF_MERGE_FAIL);
        }
        $mergedPdfData = file_get_contents($outputFileName);
        unlink($outputFileName);
        foreach ($fileNames as $fileName) {
            unlink($fileName);
        }
        return $mergedPdfData;
    }

    protected function ensureDir($dir)
    {
        if (file_exists($dir)) {
            return;
        }
        mkdir($dir, '0777', true);
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setDataplugClient(DataplugClient $dataplugClient)
    {
        $this->dataplugClient = $dataplugClient;
        return $this;
    }

    protected function setOrderLabelMapper(OrderLabelMapper $orderLabelMapper)
    {
        $this->orderLabelMapper = $orderLabelMapper;
        return $this;
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }

    protected function setOrderTrackingMapper(OrderTrackingMapper $orderTrackingMapper)
    {
        $this->orderTrackingMapper = $orderTrackingMapper;
        return $this;
    }

    protected function setOrderTrackingService(OrderTrackingService $orderTrackingService)
    {
        $this->orderTrackingService = $orderTrackingService;
        return $this;
    }

    protected function setProductDetailMapper(ProductDetailMapper $productDetailMapper)
    {
        $this->productDetailMapper = $productDetailMapper;
        return $this;
    }

    protected function setProductDetailService(ProductDetailService $productDetailService)
    {
        $this->productDetailService = $productDetailService;
        return $this;
    }
}