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
        $unixTimes = [];
        $sortValues = [];
        $sortByDateBoxes = [];
        foreach ($this->getTimelineHeadings() as $timelineHeading) {
            $title = $timelineHeading["title"];
            $timelineBoxes[$title] = $this->getTimelineBox($order, $timelineHeading);
            $unixTimes[] = $timelineBoxes[$title]['unixTime'];
            $sortValues[] = $timelineHeading["sort"];
            if (isset($timelineHeading["sortByDate"]) && $timelineHeading["sortByDate"]) {
                $sortByDateBoxes[] = $timelineHeading["title"];
            }
        }
        $timelineBoxesBySort = $timelineBoxes;
        array_multisort($timelineBoxesBySort, SORT_NUMERIC, $sortValues);
        if (empty($sortByDateBoxes)) {
            return array_values($timelineBoxesBySort);
        }

        return array_values($this->sortRelevantTimelineBoxesByDate($timelineBoxesBySort, $sortByDateBoxes));
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

    protected function sortRelevantTimelineBoxesByDate(array $timelineBoxes, array $sortByDateBoxes)
    {
        foreach ($sortByDateBoxes as $titleToMove) {
            if (!$timelineBoxes[$titleToMove]['unixTime']) {
                continue;
            }

            $sortedIndex = array_search($titleToMove, array_keys($timelineBoxes));
            $slice = array_splice($timelineBoxes, $sortedIndex, 1);
            $timelineBoxToMove = array_pop($slice);
            $count = 0;
            foreach ($timelineBoxes as $currentTitle => $timelineBox) {
                if ((int)$timelineBoxToMove['unixTime'] < (int)$timelineBox['unixTime']) {
                    break;
                }
                $count++;
            }
            if ($count < count($timelineBoxes)) {
                $index = array_search($currentTitle, array_keys($timelineBoxes));
                array_splice($timelineBoxes, $index, 0, [$timelineBoxToMove]);
            } else {
                array_push($timelineBoxes, $timelineBoxToMove);
            }
        }

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