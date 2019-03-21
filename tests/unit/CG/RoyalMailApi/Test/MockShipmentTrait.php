<?php
namespace CG\RoyalMailApi\Test;

use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Booker;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait MockShipmentTrait
{
    protected function getMockShipment(): MockObject
    {
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            // Dont mock all the methods, requires too much duplication
            ->setMethods(['getAccount', 'getPackages', 'getDeliveryAddress'])
            ->getMock();

        $shipment->expects($this->any())->method('getAccount')->willReturn($this->getMockAccount());
        $shipment->expects($this->any())->method('getPackages')->willReturn([$this->getMockPackage()]);
        // Not setting DeliveryAddress here as we might want to vary it (e.g. domestic vs international)

        return $shipment;
    }

    protected function getMockDomesticShipment(): MockObject
    {
        $shipment = $this->getMockShipment();
        $shipment->expects($this->any())->method('getDeliveryAddress')->willReturn($this->getMockDeliveryAddress(Booker::DOMESTIC_COUNTRY));
        return $shipment;
    }

    protected function getMockInternationalShipment(): MockObject
    {
        $shipment = $this->getMockShipment();
        $shipment->expects($this->any())->method('getDeliveryAddress')->willReturn($this->getMockDeliveryAddress('FR'));
        return $shipment;
    }

    abstract public function getMockBuilder($className);
    abstract public function any();
    abstract public function getMockAccount(): MockObject;
    abstract public function getMockPackage(): MockObject;
    abstract public function getMockDeliveryAddress(?string $countryCode = null): MockObject;
}