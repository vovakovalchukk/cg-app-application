<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Http\StatusCode;
use CG\Locking\Failure as LockingFailure;
use CG\Locking\Service as LockingService;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use GearmanClient;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;

    const PDF_LABEL_DIR = '/tmp/dataplug-labels';

    const LOG_CODE = 'OrderCourierLabelService';
    const LOG_CREATE_ORDER_LABEL = 'Creating OrderLabel for Order %s';
    const LOG_PDF_MERGE = 'Merging multiple label PDFs into one';
    const LOG_PDF_MERGE_WRITE_FAIL = 'Error writing PDF data to file';
    const LOG_PDF_MERGE_FAIL = 'Error merging PDF data';

    /** @var UserOUService */
    protected $userOUService;
    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var OrderLabelMapper */
    protected $orderLabelMapper;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var OrderTrackingService */
    protected $orderTrackingService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;
    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var CarrierProviderServiceRepository */
    protected $carrierProviderServiceRepo;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var LockingService */
    protected $lockingService;

    protected $orderLabelLocks = [];

    public function __construct(
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService,
        OrderTrackingService $orderTrackingService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailService $productDetailService,
        GearmanClient $gearmanClient,
        CarrierProviderServiceRepository $carrierProviderServiceRepo,
        ShippingServiceFactory $shippingServiceFactory,
        LockingService $lockingService
    ) {
        $this->userOUService = $userOuService;
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->orderLabelMapper = $orderLabelMapper;
        $this->orderLabelService = $orderLabelService;
        $this->orderTrackingService = $orderTrackingService;
        $this->productDetailMapper = $productDetailMapper;
        $this->productDetailService = $productDetailService;
        $this->gearmanClient = $gearmanClient;
        $this->carrierProviderServiceRepo = $carrierProviderServiceRepo;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->lockingService = $lockingService;
    }

    protected function getOrdersByIds(array $orderIds)
    {
        $filter = (new OrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchLinkedCollectionByFilter($filter);
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

    protected function getCarrierProviderService(Account $account)
    {
        return $this->carrierProviderServiceRepo->getProviderForAccount($account);
    }

    protected function createOrderLabelForOrder(Order $order, array $orderData, array $orderParcelsData, Account $shippingAccount)
    {
        $this->logDebug(static::LOG_CREATE_ORDER_LABEL, [$order->getId()], static::LOG_CODE);

        $serviceName = (isset($orderData['serviceName']) && $orderData['serviceName'] ? $orderData['serviceName'] : '');
        if (!$serviceName) {
            $services = $this->shippingServiceFactory->createShippingService($shippingAccount)->getShippingServicesForOrder($order);
            $serviceName = $services[$orderData['service']] ?? $orderData['service'];
        }

        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'shippingAccountId' => $shippingAccount->getId(),
            'shippingServiceCode' => $orderData['service'],
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::CREATING,
            'created' => $date->stdFormat(),
            'channelName' => $shippingAccount->getChannel(),
            'courierName' => $shippingAccount->getDisplayName(),
            'courierService' => (string)$serviceName,
            'insurance' => isset($orderData['insurance']) ? $orderData['insurance'] : '',
            'insuranceMonetary' => isset($orderData['insuranceMonetary']) ? $orderData['insuranceMonetary'] : '',
            'signature' => isset($orderData['signature']) ? $orderData['signature'] : '',
            'deliveryInstructions' => isset($orderData['deliveryInstructions']) ? $orderData['deliveryInstructions'] : '',
            'parcels' => [],
        ];

        if (empty($orderParcelsData)) {
            array_push($orderParcelsData, []);
        }

        $parcelCount = 1;
        foreach ($orderParcelsData as $parcel) {
            $orderLabelData['parcels'][] = [
                'number' => $parcelCount,
                'weight' => isset($parcel['weight']) ? $parcel['weight'] : '',
                'width' => isset($parcel['width']) ? $parcel['width'] : '',
                'height' => isset($parcel['height']) ? $parcel['height'] : '',
                'length' => isset($parcel['length']) ? $parcel['length'] : '',
            ];
            $parcelCount++;
        }
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);

        // Lock to prevent two people creating the same label at the same time
        try {
            $this->lockOrderLabel($orderLabel);
        } catch (LockingFailure $ex) {
            $this->logException($ex, 'error', __NAMESPACE__);
            $exception = new ValidationMessagesException('Locking error');
            $errorCode = StatusCode::LOCKED;
            $exception->addErrorWithField($order->getId().':'.StatusCode::LOCKED, 'Someone else appears to be creating that label');
            throw $exception;
        }

        // DO NOT save the OrderLabel at this stage. We only want to save them if the courier call is successful
        return $orderLabel;
    }

    protected function lockOrderLabel(OrderLabel $orderLabel)
    {
        $lock = $this->lockingService->lock($orderLabel);
        $this->orderLabelLocks[$orderLabel->getOrderId()] = $lock;
    }

    protected function unlockOrderLabel(OrderLabel $orderLabel)
    {
        if (isset($this->orderLabelLocks[$orderLabel->getOrderId()])) {
            $this->lockingService->unlock($this->orderLabelLocks[$orderLabel->getOrderId()]);
            unset($this->orderLabelLocks[$orderLabel->getOrderId()]);
        }
    }

    protected function unlockOrderLabels()
    {
        foreach ($this->orderLabelLocks as $lock) {
            $this->lockingService->unlock($lock);
        }
        $this->orderLabelLocks = [];
    }
}