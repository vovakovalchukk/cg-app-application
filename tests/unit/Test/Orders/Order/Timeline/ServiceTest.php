<?php
namespace Test\Orders\Order\Timeline;

use PHPUnit_Framework_TestCase;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Stdlib\Timings;
use Orders\Order\Timeline\Service as TimelineService;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    protected $timings;
    protected $timelineService;

    public function setUp()
    {
        $this->timings = $this->getMockBuilder(Timings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timelineService = new TimelineService($this->timings);
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(
            TimelineService::class,
            $this->timelineService
        );
    }
}