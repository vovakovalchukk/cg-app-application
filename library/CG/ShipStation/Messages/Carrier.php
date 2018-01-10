<?php
namespace CG\ShipStation\Messages;

class Carrier implements \JsonSerializable
{
    /** @var  string */
    protected $carrierId;
    /** @var  string */
    protected $carrierCode;

    public function __construct(string $carrierId, string $carrierCode = '')
    {
        $this->carrierId = $carrierId;
        $this->carrierCode = $carrierCode;
    }

    public function jsonSerialize()
    {
        return [
            'carrierId' => $this->getCarrierId(),
            'carrierCode' => $this->getCarrierCode(),
        ];
    }

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
