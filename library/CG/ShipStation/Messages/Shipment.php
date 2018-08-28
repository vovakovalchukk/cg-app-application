<?php
namespace CG\ShipStation\Messages;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData;
use CG\Order\Shared\Entity as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Shipment
{
    const EXTERNAL_ID_SEP = '|';

    /** @var string */
    protected $carrierId;
    /** @var string */
    protected $serviceCode;
    /** @var ShipmentAddress */
    protected $shipTo;
    /** @var string */
    protected $warehouseId;
    /** @var string */
    protected $externalShipmentId;
    /** @var string|null */
    protected $confirmation;
    /** @var Package[] */
    protected $packages;

    public function __construct(
        string $carrierId,
        string $serviceCode,
        ShipmentAddress $shipTo,
        string $warehouseId,
        string $externalShipmentId,
        ?string $confirmation,
        Package ...$packages
    ) {
        $this->carrierId = $carrierId;
        $this->serviceCode = $serviceCode;
        $this->shipTo = $shipTo;
        $this->warehouseId = $warehouseId;
        $this->externalShipmentId = $externalShipmentId;
        $this->confirmation = $confirmation;
        $this->packages = $packages;
    }

    public static function createFromOrderAndData(
        Order $order,
        OrderData $orderData,
        OrderParcelsData $parcelsData,
        Account $shipStationAccount,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): Shipment {
        $shipTo = ShipmentAddress::createFromOrder($order);
        $confirmation = $orderData->getSignature() ? 'signature' : null;
        $packages = [];
        /** @var ParcelData $parcelData */
        foreach ($parcelsData->getParcels() as $parcelData) {
            $packages[] = Package::createFromOrderAndData($order, $orderData, $parcelData, $rootOu);
        }

        return new static(
            $shippingAccount->getExternalId(),
            $orderData->getService(),
            $shipTo,
            $shipStationAccount->getExternalDataByKey('warehouseId'),
            static::getUniqueIdForOrder($order),
            $confirmation,
            ...$packages
        );
    }

    protected static function getUniqueIdForOrder(Order $order): string
    {
        // We want to link shipments back to our Orders but the external ID must be unique
        // and we ocassionally create a label more than once for an order
        return uniqid($order->getId() . static::EXTERNAL_ID_SEP);
    }

    public function toArray(): array
    {
        $array = [
            'carrier_id' => $this->getCarrierId(),
            'service_code' => $this->getServiceCode(),
            'ship_to' => $this->getShipTo()->toArray(),
            'warehouse_id' => $this->getWarehouseId(),
            'external_shipment_id' => $this->getExternalShipmentId(),
            'confirmation' => $this->getConfirmation(),
            'packages' => [],
        ];
        foreach ($this->packages as $package) {
            $array['packages'][] = $package->toArray();
        }
        return $array;
    }

    public function getCarrierId(): string
    {
        return $this->carrierId;
    }

    public function setCarrierId(string $carrierId): Shipment
    {
        $this->carrierId = $carrierId;
        return $this;
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

    public function getConfirmation(): ?string
    {
        return $this->confirmation;
    }

    public function setConfirmation(?string $confirmation): Shipment
    {
        $this->confirmation = $confirmation;
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