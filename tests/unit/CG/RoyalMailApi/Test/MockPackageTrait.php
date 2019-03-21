<?php
namespace CG\RoyalMailApi\Test;

use CG\RoyalMailApi\Shipment\Package;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

trait MockPackageTrait
{
    protected function getMockPackage(): MockObject
    {
        $package = $this->getMockBuilder(Package::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContents'])
            ->getMock();
        return $package;
    }

    abstract public function getMockBuilder($className);
}