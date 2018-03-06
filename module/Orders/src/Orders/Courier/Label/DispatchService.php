<?php
namespace Orders\Courier\Label;

use CG\Account\Shipping\Service as AccountService;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Locking\Service as LockingService;
use CG\Order\Client\Action\Service as OrderAction;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\User\ActiveUserInterface;
use CG\User\OrganisationUnit\Service as UserOUService;

class DispatchService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelDispatchService';
    const LOG_DISPATCH = 'Dispatch request for Order(s) %s, shipping Account %d';
    const LOG_DISPATCHED = 'Completed dispatch request for Order(s) %s, shipping Account %d';

    /** @var OrderAction */
    protected $orderAction;
    /** @var ActiveUserInterface */
    protected $activeUser;

    public function __construct(
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService,
        OrderTrackingService $orderTrackingService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailService $productDetailService,
        \GearmanClient $gearmanClient,
        CarrierProviderServiceRepository $carrierProviderServiceRepo,
        ShippingServiceFactory $shippingServiceFactory,
        LockingService $lockingService,
        OrderAction $orderAction,
        ActiveUserInterface $activeUser
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
        $this->orderAction = $orderAction;
        $this->activeUser = $activeUser;
    }

    public function dispatchOrders(array $orderIds, $shippingAccountId)
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_DISPATCH, [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);

        /** @var Order $order */
        foreach ($this->getOrdersByIds($orderIds) as $order) {
            $this->orderAction->dispatchOrder($order, $rootOu->getId(), $this->activeUser->getActiveUser()->getId());
        }

        $this->logDebug(static::LOG_DISPATCHED, [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }
}