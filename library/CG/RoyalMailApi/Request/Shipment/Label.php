<?php
namespace CG\RoyalMailApi\Request\Shipment;

use CG\RoyalMailApi\Request\PutAbstract;
use CG\RoyalMailApi\Response\Shipment\Label as Response;

class Label extends PutAbstract
{
    const URI_PATTERN = 'shipments/{shipmentNumber}/label';

    /** @var string */
    protected $shipmentNumber;

    public function __construct(string $shipmentNumber)
    {
        $this->shipmentNumber = $shipmentNumber;
    }

    public function getUri(): string
    {
        return str_replace('{shipmentNumber}', $this->shipmentNumber);
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function toArray(): array
    {
        return [];
    }
}