<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter;
use CG\Order\Shared\Entity;

class Service
{
    const DAY_SECONDS = 86400;
    const HOUR_SECONDS = 3600;
    const MINUTE_SECONDS = 60;

    protected $ordersTable;
    protected $orderClient;
    protected $activeUserContainer;

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        ActiveUserInterface $activeUserContainer
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function setOrdersTable($ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    public function getOrdersTable()
    {
        return $this->ordersTable;
    }

    public function setOrderClient($orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
    }

    public function setActiveUserContainer($activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function getOrders(Filter $filter)
    {
        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }

    public function getOrder($orderId)
    {
        return $this->getOrderClient()->fetch($orderId);
    }

    public function calculateTimelineBoxes(Entity $order)
    {
        $timelineHeadings = [
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

        $timelineBoxes = [];
        foreach ($timelineHeadings as $timelineHeading) {
            $unixTime = strtotime($order->$timelineHeading["get"]());
            $unixTime ?: null;
            $timelineBoxes['timelineBoxes'][] = [
                'title' => $timelineHeading["title"],
                'subtitle' => $order->$timelineHeading["get"]() ? date("jS M Y", $unixTime) : "Order Not Yet",
                'extraText' => $order->$timelineHeading["get"]() ? date("h:ia", $unixTime) : "Order Not Yet",
                'colour' => $order->$timelineHeading["get"]() ? "green" : "grey",
                'unixTime' => $unixTime
            ];
            $unixTimes[] = $unixTime;
        }
        array_multisort($timelineBoxes["timelineBoxes"], SORT_NUMERIC, $unixTimes);
        $firstTimelineBox = current($timelineBoxes["timelineBoxes"]);

        $previousBox = "";
        foreach ($timelineBoxes["timelineBoxes"] as $index => $box) {
            if ($previousBox) {
                if ($previousBox["unixTime"] && $box["unixTime"]) {
                    $difference = $box["unixTime"] - $previousBox["unixTime"];
                    if ($difference < static::HOUR_SECONDS) {
                        $differenceString = round($difference / static::MINUTE_SECONDS) . " Minutes";
                    } elseif ($difference < static::DAY_SECONDS) {
                        $differenceString = round($difference / static::HOUR_SECONDS) . " Hours";
                    } else {
                        $differenceString = round($difference / static::DAY_SECONDS) . " Days";
                    }
                } else {
                    $differenceString = "";
                }
                $timelineBoxes['timelineTimes'][] = [
                    'status' => $previousBox['unixTime'] ? 'ok' : 'none',
                    'time' => $differenceString
                ];
            }
            $previousBox = $box;
        }
        $timelineBoxes['timelineTimes'][] = [
            'status' => $previousBox['unixTime'] ? 'ok' : 'none',
            'time' => ''
        ];

        $totalTimeUnix = end($timelineBoxes["timelineBoxes"])['unixTime'] - $firstTimelineBox['unixTime'];
        $totalTime = "";
        if ($totalTimeUnix >= static::DAY_SECONDS) {
            $days = floor($totalTimeUnix / static::DAY_SECONDS);
            $totalTime .= $days . " Days ";
            $totalTimeUnix -= ($days * static::DAY_SECONDS);
        }
        if ($totalTimeUnix >= static::HOUR_SECONDS) {
            $hours = floor($totalTimeUnix / static::HOUR_SECONDS);
            $totalTime .= $hours . " Hours ";
            $totalTimeUnix -= ($hours * static::HOUR_SECONDS);
        }
        if ($totalTimeUnix >= static::MINUTE_SECONDS) {
            $minutes = floor($totalTimeUnix / static::MINUTE_SECONDS);
            $totalTime .= $minutes . " Minutes ";
        }
        $timelineBoxes['timelineTotal'] = end($timelineBoxes["timelineBoxes"])['unixTime'] ? $totalTime : "Order Not Yet Completed";
        return $timelineBoxes;
    }
}