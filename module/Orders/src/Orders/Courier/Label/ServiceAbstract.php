<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use GearmanClient;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;

    const PDF_LABEL_DIR = '/tmp/dataplug-labels';

    const LOG_CODE = 'OrderCourierLabelService';
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
        ShippingServiceFactory $shippingServiceFactory
    ) {
        $this->setUserOUService($userOuService)
            ->setOrderService($orderService)
            ->setAccountService($accountService)
            ->setOrderLabelMapper($orderLabelMapper)
            ->setOrderLabelService($orderLabelService)
            ->setOrderTrackingService($orderTrackingService)
            ->setProductDetailMapper($productDetailMapper)
            ->setProductDetailService($productDetailService)
            ->setGearmanClient($gearmanClient)
            ->setCarrierProviderServiceRepo($carrierProviderServiceRepo)
            ->setShippingServiceFactory($shippingServiceFactory);
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

    protected function getCarrierProviderService(Account $account)
    {
        return $this->carrierProviderServiceRepo->getProviderForAccount($account);
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

    protected function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    protected function setCarrierProviderServiceRepo(CarrierProviderServiceRepository $carrierProviderServiceRepo)
    {
        $this->carrierProviderServiceRepo = $carrierProviderServiceRepo;
        return $this;
    }

    protected function setShippingServiceFactory(ShippingServiceFactory $shippingServiceFactory)
    {
        $this->shippingServiceFactory = $shippingServiceFactory;

        return $this;
    }
}