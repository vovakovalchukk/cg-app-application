<?php
namespace CG\ShipStation\Carrier\Rates;

use CG\Channel\Shipping\Provider\Service\ShippingRateInterface;

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

    public static function fromShipEngineRateData(array $data): Entity
    {
        return new static(
            $data['rate_id'],
            $data['service_type'],
            $data['service_code'],
            $data['shipping_amount']['amount'],
            $data['shipping_amount']['currency']
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
}