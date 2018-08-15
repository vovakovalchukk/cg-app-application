<?php
namespace CG\ShipStation\Carrier\Rates\Usps;

use Predis\Client as PredisClient;

class ShipmentIdStorage
{
    const REDIS_KEY_TEMPLATE = 'usps_shipment_id_from_rates_%s';
    /** @var PredisClient */
    protected $redisClient;

    public function __construct(PredisClient $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public function put(string $orderId, string $shippmentId): ShipmentIdStorage
    {
        $this->redisClient->set($this->buildKeyForOrderId($orderId), $shippmentId);
        return $this;
    }

    public function get(string $orderId): string
    {
        return $this->redisClient->get($this->buildKeyForOrderId($orderId));
    }

    public function delete(string $orderId): void
    {
        $this->redisClient->delete($this->buildKeyForOrderId($orderId));
    }

    protected function buildKeyForOrderId(string $orderId)
    {
        return sprintf(static::REDIS_KEY_TEMPLATE, $orderId);
    }
}