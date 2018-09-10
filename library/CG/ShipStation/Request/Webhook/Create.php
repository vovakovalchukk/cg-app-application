<?php
namespace CG\ShipStation\Request\Webhook;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Webhook\Create as Response;

class Create extends RequestAbstract
{
    const EVENT_CARRIER_CONNECTED = 'carrier_connected';

    const METHOD = 'POST';
    const URI = '/environment/webhooks';

    /** @var string */
    protected $event;
    /** @var string */
    protected $url;

    public function __construct(string $event, string $url)
    {
        $this->event = $event;
        $this->url = $url;
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getEvent(),
            'url' => $this->getUrl(),
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}