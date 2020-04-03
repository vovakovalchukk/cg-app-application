<?php
namespace Orders\Courier\Label\Cancel;

use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Entity as Order;

class DefaultAction implements CancelActionInterface
{
    /** @var OrderTrackingService */
    protected $orderTrackingService;

    public function __construct(OrderTrackingService $orderTrackingService)
    {
        $this->orderTrackingService = $orderTrackingService;
    }

    public function postTrackingNumberRemovalAction(Order $order): void
    {
        $this->orderTrackingService->createGearmanJob($order);
    }
}