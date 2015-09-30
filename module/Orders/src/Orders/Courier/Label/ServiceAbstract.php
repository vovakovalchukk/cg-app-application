<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Dataplug\Client as DataplugClient;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;

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

    public function __construct(
        Mapper $mapper,
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        DataplugClient $dataplugClient,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService
    ) {
        $this->setMapper($mapper)
            ->setUserOUService($userOuService)
            ->setOrderService($orderService)
            ->setAccountService($accountService)
            ->setDataplugClient($dataplugClient)
            ->setOrderLabelMapper($orderLabelMapper)
            ->setOrderLabelService($orderLabelService)
            ->setOrderTrackingMapper($orderTrackingMapper)
            ->setOrderTrackingService($orderTrackingService);
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
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $filter = (new OrderLabelFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setOrderId([$order->getId()])
            ->setStatus($labelStatusesNotCancelled);
        $orderLabels = $this->orderLabelService->fetchCollectionByFilter($filter);
        $orderLabels->rewind();
        return $orderLabels->current();
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
}