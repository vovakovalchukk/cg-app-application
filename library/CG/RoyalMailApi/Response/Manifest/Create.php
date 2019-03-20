<?php
namespace CG\RoyalMailApi\Response\Manifest;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\ManifestInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use CG\RoyalMailApi\ResponseInterface;
use CG\Template\DocumentInterface;
use stdClass;

class Create implements ResponseInterface, FromJsonInterface, ManifestInterface
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
        $shipments = [];
        foreach (array($json->shipments ?? []) as $shipment) {
            $shipments[] = Shipment::fromJson($shipment);
        }

        return new static(
            (int) $json->batchNumber,
            (int) $json->count,
            $json->manifest ?? null,
            $shipments
        );
    }

    public function getType()
    {
        return \CG\CourierAdapter\DocumentInterface::TYPE_PDF;
    }

    public function getData()
    {
        return $this->getManifest();
    }

    public function getAccount()
    {
        $this->account;
    }

    public function getReference()
    {
        return $this->getBatchNumber();
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
