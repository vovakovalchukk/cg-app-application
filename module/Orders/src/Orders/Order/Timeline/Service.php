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
            "timelineTotal" => $this->getTimelineTotal($orderedTimelineBoxes)
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
                if (!$currentBox['unixTime'] || (int)$timelineBoxToSort['unixTime'] < (int)$currentBox['unixTime']) {
                    break;
                }
                $count++;
            }
            $this->insertTimelineBox($timelineBoxes, $count, $timelineBoxToSort);
        }

        return $timelineBoxes;
    }

    protected function extractTimelineBox(array &$timelineBoxes, $timelineBoxIndex)
    {
        $slice = array_splice($timelineBoxes, $timelineBoxIndex, 1);
        return array_pop($slice);
    }

    protected function insertTimelineBox(array &$timelineBoxes, $timelineBoxIndex, array $timelineBox)
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
        $lastAction = 0;
        foreach ($timelineBoxes as $box) {
            if ($previousBox) {
                if ($box["unixTime"] && $lastAction) {
                    $difference = $box["unixTime"] - $lastAction;
                    $time = $this->getTimings()->secondsIntoOneOfMinutesHoursDays($difference);
                } else {
                    $time = 'N/A';
                }
                $timelineTimes[] = [
                    'status' => $previousBox['unixTime'] ? 'ok' : 'none',
                    'time' => $time
                ];
            }
            $lastAction = $box['unixTime'] ?: $lastAction;
            $previousBox = $box;
        }
        $timelineTimes[] = [
            'status' => $previousBox['unixTime'] ? 'ok' : 'none',
            'time' => ''
        ];
        return $timelineTimes;
    }

    protected function getTimelineTotal(array $timelineBoxes)
    {
        $start = $end = null;
        foreach ($timelineBoxes as $timelineBox) {
            if (!$timelineBox['unixTime']) {
                continue;
            }
            if (!$start) {
                $start = $timelineBox['unixTime'];
            }
            if ($timelineBox['unixTime'] > (int)$end) {
                $end = $timelineBox['unixTime'];
            }
        }
        if (!$start || !$end) {
            return '';
        }

        $seconds = $end - $start;
        return $this->getTimings()->secondsIntoMinutesHoursDays($seconds);
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