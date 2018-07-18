<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use Throwable;

class LabelResults
{
    /** @var array */
    protected $responses = [];
    /** @var array */
    protected $throwables = [];

    public function addResponse(string $orderId, LabelResponse $response): LabelResults
    {
        $this->responses[$orderId] = $response;
        return $this;
    }

    public function addThrowable(string $orderId, Throwable $throwable): LabelResults
    {
        $this->throwables[$orderId] = $throwable;
        return $this;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getThrowables(): array
    {
        return $this->throwables;
    }
}