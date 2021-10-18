<?php
namespace CG\ShipStation\Messages;

use CG\Order\Shared\Courier\Label\OrderData;

class AdvancedOptions
{
    /** @var bool */
    protected $deliveredDutyPaid;

    public function __construct(bool $deliveredDutyPaid)
    {
        $this->deliveredDutyPaid = $deliveredDutyPaid;
    }

    public static function createFromOrder(OrderData $orderData): AdvancedOptions
    {
        return new self($orderData->isDeliveredDutyPaid());
    }

    public function toArray(): array
    {
        return [
            'delivered_duty_paid' => $this->isDeliveredDutyPaid()
        ];
    }

    public function isDeliveredDutyPaid(): bool
    {
        return $this->deliveredDutyPaid;
    }

    public function setDeliveredDutyPaid(bool $deliveredDutyPaid): AdvancedOptions
    {
        $this->deliveredDutyPaid = $deliveredDutyPaid;
        return $this;
    }
}