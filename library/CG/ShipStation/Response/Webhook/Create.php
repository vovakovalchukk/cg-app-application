<?php
namespace CG\ShipStation\Response\Webhook;

use CG\ShipStation\ResponseAbstract;

class Create extends ResponseAbstract
{
    /** @var string */
    protected $webhookId;

    public function __construct(string $webhookId)
    {
        $this->webhookId = $webhookId;
    }

    protected static function build($decodedJson)
    {
        return new self($decodedJson->webhook_id);
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }
}