<?php
namespace Test\Orders\Order\Timeline;

use PHPUnit_Framework_TestCase;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Stdlib\Timings;

class Service extends PHPUnit_Framework_TestCase
{
    protected $timings;

    public function setUp()
    {
        $this->timings = $this->getMockBuilder(Timings::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}