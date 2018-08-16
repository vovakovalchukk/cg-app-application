<?php
namespace CG\ShipStation\Carrier\Rates\Usps;

use Predis\Client as PredisClient;

class ShipmentIdStorage
{
    const REDIS_KEY_TEMPLATE = 'usps_shipment_id_from_rates_%s';
    const REDIS_EXPIRY_TIME = 3600;

    /** @var PredisClient */
    protected $redisClient;

    public function __construct(PredisClient $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public function save(string $orderId, string $shipmentId): ShipmentIdStorage
    {
        $this->redisClient->setex($this->buildKeyForOrderId($orderId), static::REDIS_EXPIRY_TIME, $shipmentId);
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