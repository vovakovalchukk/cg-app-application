<?php
namespace CG\ShipStation\Messages;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Entity as Order;

class Shipment
{
    const EXTERNAL_ID_SEP = '|';

    /** @var string */
    protected $serviceCode;
    /** @var ShipmentAddress */
    protected $shipTo;
    /** @var string */
    protected $warehouseId;
    /** @var string */
    protected $externalShipmentId;
    /** @var Package[] */
    protected $packages;

    public function __construct(string $serviceCode, ShipmentAddress $shipTo, string $warehouseId, string $externalShipmentId, Package ...$packages)
    {
        $this->serviceCode = $serviceCode;
        $this->shipTo = $shipTo;
        $this->warehouseId = $warehouseId;
        $this->externalShipmentId = $externalShipmentId;
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
            static::getUniqueIdForOrder($order),
            ...$packages
        );
    }

    protected static function getUniqueIdForOrder(Order $order): string
    {
        // We want to link shipments back to our Orders but the external ID must be unique
        // and we ocassionally create a label more than once for an order
        return uniqid($order->getId() . static::EXTERNAL_ID_SEP);
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

    public function getExternalShipmentId(): string
    {
        return $this->externalShipmentId;
    }

    public function setExternalShipmentId(string $externalShipmentId): Shipment
    {
        $this->externalShipmentId = $externalShipmentId;
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