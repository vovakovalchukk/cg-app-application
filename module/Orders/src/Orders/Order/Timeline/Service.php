<?php
namespace Orders\Order\Timeline;

use CG\Order\Shared\Entity as OrderEntity;
use CG\Stdlib\Timings;

class Service
{
    protected $timings;
    protected $timelineHeadings = [
        [
            "get" => "getPurchaseDate",
            "title" => "Purchase Date",
            "extraText" => "Purchased"
        ],
        [
            "get" => "getPaymentDate",
            "title" => "Payment Date",
            "extraText" => "Paid For"
        ],
        [
            "get" => "getPrintedDate",
            "title" => "Printed Date",
            "extraText" => "Printed"
        ],
        [
            "get" => "getDispatchDate",
            "title" => "Dispatch Date",
            "extraText" => "Dispatched"
        ]
    ];

    public function __construct(Timings $timings)
    {
        $this->setTimings($timings);
    }

    public function getTimeline(OrderEntity $order)
    {
        $orderedTimelineBoxes = $this->getOrderedTimelineBoxes($order);
        $timeline = [
            "timelineBoxes" => $orderedTimelineBoxes,
            "timelineTimes" => $this->getTimelineTimes($orderedTimelineBoxes),
            "timelineTotal" => $this->getTimelineTotal($orderedTimelineBoxes[0]['unixTime'], end($orderedTimelineBoxes)['unixTime'])
        ];
        return $timeline;
    }

    protected function getOrderedTimelineBoxes(OrderEntity $order)
    {
        $timelineBoxes = [];
        foreach ($this->getTimelineHeadings() as $timelineHeading) {
            $unixTime = strtotime($order->$timelineHeading["get"]()) ?: null;
            $timelineBoxes[] = [
                'title' => $timelineHeading["title"],
                'subtitle' => $unixTime ? date("jS M Y", $unixTime) : "Order Not Yet",
                'extraText' => $unixTime ? date("h:ia", $unixTime) : "Order Not Yet",
                'colour' => $unixTime ? "green" : "grey",
                'unixTime' => $unixTime
            ];
            $unixTimes[] = $unixTime;
        }
        array_multisort($timelineBoxes, SORT_NUMERIC, $unixTimes);
        return $timelineBoxes;
    }

    protected function getTimelineTimes(array $timelineBoxes)
    {
        $previousBox = "";
        foreach ($timelineBoxes as $box) {
            if ($previousBox) {
                $difference = $box["unixTime"] - $previousBox["unixTime"];
                $timelineTimes[] = [
                    'status' => $previousBox['unixTime'] ? 'ok' : 'none',
                    'time' => $box["unixTime"] ? $this->getTimings()->secondsIntoOneOfMinutesHoursDays($difference) : ""
                ];
            }
            $previousBox = $box;
        }
        $timelineTimes[] = [
            'status' => $previousBox['unixTime'] ? 'ok' : 'none',
            'time' => ''
        ];
        return $timelineTimes;
    }

    protected function getTimelineTotal($start, $end)
    {
        $seconds = $end - $start;
        return $end ? $this->getTimings()->secondsIntoMinutesHoursDays($seconds) : "Order Not Yet Completed";
    }

    protected function setTimings(Timings $timings)
    {
        $this->timings = $timings;
        return $this;
    }

    protected function getTimings()
    {
        return $this->timings;
    }

    protected function setTimelineHeadings(array $timelineHeadings)
    {
        $this->timelineHeadings = $timelineHeadings;
        return $this;
    }

    protected function getTimelineHeadings()
    {
        return $this->timelineHeadings;
    }
}