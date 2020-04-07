<?php
namespace Orders\Courier\Label;

use CG\Account\Shipping\Service as AccountService;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Locking\Service as LockingService;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Tracking\Cancel\Factory as CancelActionFactory;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOUService;
use GearmanClient;

class CancelService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelCancelService';
    const LOG_CANCEL = 'Cancel request for Order(s) %s, shipping Account %d';
    const LOG_CANCEL_DONE = 'Completed cancel request for Order(s) %s, shipping Account %d';
    const LOG_UPDATE = 'Updating OrderLabel to cancelled for Order %s';
    const LOG_REMOVE_TRACKING = 'Removing tracking numbers for Order %s.';

    /* @var CancelActionFactory */
    protected $cancelActionFactory;

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
        LockingService $lockingService,
        CancelActionFactory $cancelActionFactory
    ) {
        parent::__construct(
            $userOuService,
            $orderService,
            $accountService,
            $orderLabelMapper,
            $orderLabelService,
            $orderTrackingService,
            $productDetailMapper,
            $productDetailService,
            $gearmanClient,
            $carrierProviderServiceRepo,
            $shippingServiceFactory,
            $lockingService
        );

        $this->cancelActionFactory = $cancelActionFactory;
    }

    public function cancelForOrders(array $orderIds, $shippingAccountId)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CANCEL, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetchShippingAccount((int) $shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $orderLabels = $this->getOrderLabelsForOrders($orders);

        $this->getCarrierProviderService($shippingAccount)->cancelOrderLabels($orderLabels, $orders, $shippingAccount);
        foreach ($orderLabels as $orderLabel) {
            $order = $orders->getById($orderLabel->getOrderId());
            $this->cancelOrderLabel($orderLabel);
            $this->removeOrderTracking($order);
        }

        $this->logDebug(static::LOG_CANCEL_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function cancelOrderLabel(OrderLabel $orderLabel)
    {
        $this->logDebug(static::LOG_UPDATE, [$orderLabel->getOrderId()], static::LOG_CODE, ['order' => $orderLabel->getOrderId()]);
        $orderLabel->setStatus(OrderLabelStatus::CANCELLED);
        $this->orderLabelService->save($orderLabel);
    }

    protected function removeOrderTracking(Order $order)
    {
        $this->logDebug(static::LOG_REMOVE_TRACKING, [$order->getId()], static::LOG_CODE, ['order' => $order->getId()]);
        foreach ($order->getChannelUpdatableOrders() as $updatableOrder) {
            try {
                $this->orderTrackingService->removeByOrderId($updatableOrder->getId());
                $orderTrackings = $updatableOrder->getTrackings();
                $orderTrackings->removeAll($orderTrackings);
                (($this->cancelActionFactory)($updatableOrder->getChannel()))->postTrackingNumberRemovalAction($updatableOrder);
            } catch (NotFound $e) {
                // No-op
            }
        }
    }
}
