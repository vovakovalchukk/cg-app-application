<?php
namespace CG\ShipStation\Carrier\Rates;

use CG\Channel\Shipping\Provider\Service\ShippingRateInterface;
use CG\ShipStation\Messages\Rate;

class Entity implements ShippingRateInterface
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $name;
    /** @var string */
    protected $serviceCode;
    /** @var float */
    protected $cost;
    /** @var string */
    protected $currencyCode;

    public function __construct(string $id, string $name, string $serviceCode, float $cost, string $currencyCode)
    {
        $this->id = $id;
        $this->name = $name;
        $this->serviceCode = $serviceCode;
        $this->cost = $cost;
        $this->currencyCode = $currencyCode;
    }

    public static function fromShipEngineRate(Rate $rate): Entity
    {
        return new static(
            $rate->getRateId(),
            $rate->getServiceType(),
            $rate->getServiceCode(),
            $rate->getShippingAmount()->getAmount(),
            $rate->getShippingAmount()->getCurrency()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    // Cost can be overridden when we add our margin on to it
    public function setCost(float $cost): Entity
    {
        $this->cost = $cost;
        return $this;
    }
}