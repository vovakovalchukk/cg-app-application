<?php
namespace OrdersTest\Orders\Order\Timeline;

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
        $this->timings
            ->expects($this->any())
            ->method($this->logicalOr('secondsIntoOneOfMinutesHoursDays', 'secondsIntoMinutesHoursDays'))
            ->will($this->returnArgument(0));

        $this->timelineService = new TimelineService($this->timings);
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(
            TimelineService::class,
            $this->timelineService
        );
    }

    public function testGetTimelineWithConsecutiveDatesAreCorrect()
    {
        $order = $this->getMockOrder();
        $purchaseDate = '2014-01-01 12:00:00';
        $paymentDate = '2014-01-01 12:10:00';
        $printedDate = '2014-01-01 16:25:00';
        $dispatchDate = '2014-01-01 16:30:00';
        $this->mockOrderDate($order, 'purchaseDate', $purchaseDate)
            ->mockOrderDate($order, 'paymentDate', $paymentDate)
            ->mockOrderDate($order, 'printedDate', $printedDate)
            ->mockOrderDate($order, 'dispatchDate', $dispatchDate);

        $timeline = $this->timelineService->getTimeline($order);
        $timelineBoxes = $timeline['timelineBoxes'];
        $timelineTimes = $timeline['timelineTimes'];
        $timelineTotal = $timeline['timelineTotal'];

        $this->assertEquals(strtotime($purchaseDate), $timelineBoxes[0]['unixTime'], 'PurchaseDate not first');
        $this->assertEquals(strtotime($paymentDate), $timelineBoxes[1]['unixTime'], 'PaymentDate not second');
        $this->assertEquals(strtotime($printedDate), $timelineBoxes[2]['unixTime'], 'PrintedDate not third');
        $this->assertEquals(strtotime($dispatchDate), $timelineBoxes[3]['unixTime'], 'DispatchDate not fourth');

        $this->assertEquals($timelineBoxes[1]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTimes[0]['time'], 'Purchase -> Payment time is incorrect');
        $this->assertEquals($timelineBoxes[2]['unixTime'] - $timelineBoxes[1]['unixTime'], $timelineTimes[1]['time'], 'Payment -> Print time is incorrect');
        $this->assertEquals($timelineBoxes[3]['unixTime'] - $timelineBoxes[2]['unixTime'], $timelineTimes[2]['time'], 'Print -> Dispatch time is incorrect');

        $this->assertEquals($timelineBoxes[3]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTotal, 'Total time is incorrect');
    }

    public function testGetTimelineWithNonConsecutiveDatesAreReordered()
    {
        $order = $this->getMockOrder();
        $purchaseDate = '2014-01-01 12:00:00';
        $printedDate = '2014-01-01 12:05:00';
        $dispatchDate = '2014-01-01 16:30:00';
        $paymentDate = '2014-01-02 10:00:00';
        $this->mockOrderDate($order, 'purchaseDate', $purchaseDate)
            ->mockOrderDate($order, 'paymentDate', $paymentDate)
            ->mockOrderDate($order, 'printedDate', $printedDate)
            ->mockOrderDate($order, 'dispatchDate', $dispatchDate);

        $timeline = $this->timelineService->getTimeline($order);
        $timelineBoxes = $timeline['timelineBoxes'];
        $timelineTimes = $timeline['timelineTimes'];
        $timelineTotal = $timeline['timelineTotal'];

        $this->assertEquals(strtotime($purchaseDate), $timelineBoxes[0]['unixTime'], 'PurchaseDate not first');
        $this->assertEquals(strtotime($printedDate), $timelineBoxes[1]['unixTime'], 'PrintedDate not second');
        $this->assertEquals(strtotime($paymentDate), $timelineBoxes[2]['unixTime'], 'PaymentDate not third');
        $this->assertEquals(strtotime($dispatchDate), $timelineBoxes[3]['unixTime'], 'DispatchDate not fourth');

        $this->assertEquals($timelineBoxes[1]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTimes[0]['time'], 'Purchase -> Print time is incorrect');
        $this->assertEquals($timelineBoxes[2]['unixTime'] - $timelineBoxes[1]['unixTime'], $timelineTimes[1]['time'], 'Print -> Dispatch time is incorrect');
        $this->assertEquals($timelineBoxes[3]['unixTime'] - $timelineBoxes[2]['unixTime'], $timelineTimes[2]['time'], 'Dispatch -> Print time is incorrect');

        $this->assertEquals($timelineBoxes[2]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTotal, 'Total time is incorrect');
    }

    public function testGetTimelineWithMissingDatesAreCorrect()
    {
        $order = $this->getMockOrder();
        $purchaseDate = '2014-01-01 12:00:00';
        $printedDate = null;
        $dispatchDate = '2014-01-01 16:30:00';
        $paymentDate = null;
        $this->mockOrderDate($order, 'purchaseDate', $purchaseDate)
            ->mockOrderDate($order, 'paymentDate', $paymentDate)
            ->mockOrderDate($order, 'printedDate', $printedDate)
            ->mockOrderDate($order, 'dispatchDate', $dispatchDate);

        $timeline = $this->timelineService->getTimeline($order);
        $timelineBoxes = $timeline['timelineBoxes'];
        $timelineTimes = $timeline['timelineTimes'];
        $timelineTotal = $timeline['timelineTotal'];

        $this->assertEquals(strtotime($purchaseDate), $timelineBoxes[0]['unixTime'], 'PurchaseDate not first');
        $this->assertEquals(null, $timelineBoxes[1]['unixTime'], 'PaymentDate set or not second');
        $this->assertEquals(strtotime($dispatchDate), $timelineBoxes[2]['unixTime'], 'DispatchDate not third');
        $this->assertEquals(null, $timelineBoxes[3]['unixTime'], 'PrintedDate set not fourth');

        $this->assertEquals('N/A', $timelineTimes[0]['time'], 'Purchase -> Payment time is set');
        $this->assertEquals($timelineBoxes[2]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTimes[1]['time'], 'Payment -> Dispatch time is incorrect');
        $this->assertEquals('N/A', $timelineTimes[2]['time'], 'Dispatch -> Print time is set');

        $this->assertEquals($timelineBoxes[2]['unixTime'] - $timelineBoxes[0]['unixTime'], $timelineTotal, 'Total time is incorrect');
    }

    protected function getMockOrder()
    {
        $order = $this->getMockBuilder(OrderEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $order;
    }

    protected function mockOrderDate($order, $dateName, $dateVal)
    {
        $order->expects($this->any())
            ->method('get'.ucfirst($dateName))
            ->will($this->returnValue($dateVal));
        return $this;
    }
}