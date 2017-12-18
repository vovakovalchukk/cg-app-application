<?php
namespace CG\ShipStation\Messages;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Entity as Order;

class Shipment
{
    /** @var string */
    protected $serviceCode;
    /** @var ShipmentAddress */
    protected $shipTo;
    /** @var string */
    protected $warehouseId;
    /** @var Package[] */
    protected $packages;

    public function __construct(string $serviceCode, ShipmentAddress $shipTo, string $warehouseId, Package ...$packages)
    {
        $this->serviceCode = $serviceCode;
        $this->shipTo = $shipTo;
        $this->warehouseId = $warehouseId;
        $this->packages = $packages;
    }

    public static function createFromOrderAndData(
        Order $order,
        array $orderData,
        array $parcelsData,
        Account $shipStationAccount
    ): Shipment {
        $shipTo = ShipmentAddress::createFromOrder($order);
        $packages = [];
        foreach ($parcelsData as $parcelData) {
            $packages[] = Package::createFromOrderAndData($order, $orderData, $parcelData);
        }

        return new static(
            $orderData['service'],
            $shipTo,
            $shipStationAccount->getExternalDataByKey('warehouseId'),
            ...$packages
        );
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    /**
     * @return self
     */
    public function setServiceCode(string $serviceCode)
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function getShipTo(): ShipmentAddress
    {
        return $this->shipTo;
    }

    /**
     * @return self
     */
    public function setShipTo(ShipmentAddress $shipTo)
    {
        $this->shipTo = $shipTo;
        return $this;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    /**
     * @return self
     */
    public function setWarehouseId(string $warehouseId)
    {
        $this->warehouseId = $warehouseId;
        return $this;
    }

    /**
     * @return Package[]
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @return self
     */
    public function setPackages(Package ...$packages)
    {
        $this->packages = $packages;
        return $this;
    }
}