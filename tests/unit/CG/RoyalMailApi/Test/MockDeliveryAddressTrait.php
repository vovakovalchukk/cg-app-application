<?php
namespace CG\RoyalMailApi\Test;

use CG\CourierAdapter\Address;
use CG\RoyalMailApi\Shipment\Booker;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait MockDeliveryAddressTrait
{
    protected function getMockDeliveryAddress(?string $countryCode = null): MockObject
    {
        $deliveryAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getISOAlpha2CountryCode'])
            ->getMock();
        $deliveryAddress->expects($this->any())
            ->method('getISOAlpha2CountryCode')
            ->willReturn($countryCode ?? Booker::DOMESTIC_COUNTRY);
        return $deliveryAddress;
    }

    abstract public function getMockBuilder($className);
    abstract public function any();
}