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
            "extraText" => "Purchased",
            "sort" => 10
        ],
        [
            "get" => "getPaymentDate",
            "title" => "Payment Date",
            "extraText" => "Paid For",
            "sort" => 20
        ],
        [
            "get" => "getDispatchDate",
            "title" => "Dispatch Date",
            "extraText" => "Dispatched",
            "sort" => 30
        ],
        [
            "get" => "getPrintedDate",
            "title" => "Printed Date",
            "extraText" => "Printed",
            "sort" => 40,
            "sortByDate" => true
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
        $sortValues = [];
        $sortByDateBoxes = [];
        $count = 0;
        foreach ($this->getTimelineHeadings() as $timelineHeading) {
            $timelineBox = $this->getTimelineBox($order, $timelineHeading);
            $timelineBoxes[] = $timelineBox;
            $sortValues[] = $timelineHeading["sort"];
            if (isset($timelineHeading["sortByDate"]) && $timelineHeading["sortByDate"]) {
                $sortByDateBoxes[$count] = $timelineBox;
            }
            $count++;
        }

        array_multisort($timelineBoxes, SORT_NUMERIC, $sortValues);
        if (empty($sortByDateBoxes)) {
            return $timelineBoxes;
        }

        return $this->sortGivenTimelineBoxesByDate($timelineBoxes, $sortByDateBoxes);
    }

    protected function getTimelineBox(OrderEntity $order, array $timelineHeading)
    {
        $unixTime = strtotime($order->$timelineHeading["get"]()) ?: null;
        $timelineBox = [
            'title' => $timelineHeading["title"],
            'subtitle' => $unixTime ? date("jS M Y", $unixTime) : "N/A",
            'extraText' => $unixTime ? date("h:ia", $unixTime) : "",
            'colour' => $unixTime ? "green" : "light-grey",
            'unixTime' => $unixTime
        ];
        return $timelineBox;
    }

    protected function sortGivenTimelineBoxesByDate(array $timelineBoxes, array $sortByDateBoxes)
    {
        foreach ($sortByDateBoxes as $sortByDateBoxIndex => $sortByDateBox) {
            if (!$sortByDateBox['unixTime']) {
                continue;
            }

            $timelineBoxToSort = $this->extractTimelineBox($timelineBoxes, $sortByDateBoxIndex);
            $count = 0;
            foreach ($timelineBoxes as $currentBox) {
                if ((int)$timelineBoxToSort['unixTime'] < (int)$currentBox['unixTime']) {
                    break;
                }
                $count++;
            }
            $this->insertTimelineBox($timelineBoxes, $count, $timelineBoxToSort);
        }

        return $timelineBoxes;
    }

    protected function extractTimelineBox(&$timelineBoxes, $timelineBoxIndex)
    {
        $slice = array_splice($timelineBoxes, $timelineBoxIndex, 1);
        return array_pop($slice);
    }

    protected function insertTimelineBox(&$timelineBoxes, $timelineBoxIndex, $timelineBox)
    {
        if ($timelineBoxIndex < count($timelineBoxes)) {
            array_splice($timelineBoxes, $timelineBoxIndex, 0, [$timelineBox]);
        } else {
            array_push($timelineBoxes, $timelineBox);
        }
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