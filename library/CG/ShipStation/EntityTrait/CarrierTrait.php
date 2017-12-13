<?php
namespace CG\ShipStation\EntityTrait;

trait CarrierTrait
{
    /** @var  string */
    protected $carrierId;
    /** @var  string */
    protected $carrierCode;

    public function getCarrierId(): ?string
    {
        return $this->carrierId;
    }

    public function setCarrierId(string $carrierId)
    {
        $this->carrierId = $carrierId;
        return $this;
    }

    public function getCarrierCode(): ?string
    {
        return $this->carrierCode;
    }

    public function setCarrierCode(string $carrierCode)
    {
        $this->carrierCode = $carrierCode;
        return $this;
    }
}
