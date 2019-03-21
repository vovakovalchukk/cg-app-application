<?php
namespace CG\RoyalMailApi\Response\Manifest;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Response\FromJsonInterface;
use CG\RoyalMailApi\ResponseInterface;
use stdClass;

class Create implements ResponseInterface, FromJsonInterface
{
    /** @var integer */
    protected $batchNumber;
    /** @var integer */
    protected $count;
    /** @var ?string */
    protected $manifest;
    /** @var Shipment[] */
    protected $shipments;
    /** @var Account */
    protected $account;

    public function __construct(int $batchNumber, int $count, ?string $manifest, array $shipments)
    {
        $this->batchNumber = $batchNumber;
        $this->count = $count;
        $this->manifest = $manifest;
        $this->shipments = $shipments;
    }

    public static function fromJson(stdClass $json)
    {
        if (!isset($json->batchNumber, $json->count, $json->shipments)) {
            throw new \InvalidArgumentException('Create manifest response has an invalid format');
        }

        $shipments = [];
        foreach ($json->shipments as $shipment) {
            $shipments[] = Shipment::fromJson($shipment);
        }

        return new static(
            (int) $json->batchNumber,
            (int) $json->count,
            $json->manifest ?? null,
            $shipments
        );
    }

    public function getBatchNumber(): int
    {
        return $this->batchNumber;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getManifest(): ?string
    {
        return $this->manifest;
    }

    public function getShipments(): array
    {
        return $this->shipments;
    }
}
