<?php
namespace CG\RoyalMailApi\Request\Shipment;

use CG\RoyalMailApi\Request\PutAbstract;
use CG\RoyalMailApi\Response\Shipment\Documents as Response;

class Documents extends PutAbstract
{
    const URI_PATTERN = 'shipments/{shipmentNumber}/documents';
    const DOCUMENT_CN22 = 'CN22';
    const DOCUMENT_CN23 = 'CN23';

    /** @var string */
    protected $shipmentNumber;
    /** @var string */
    protected $documentName;

    public function __construct(string $shipmentNumber, string $documentName)
    {
        $this->shipmentNumber = $shipmentNumber;
        $this->documentName = $documentName;
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
        return [
            'documentName' => $this->getDocumentName()
        ];
    }

    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }

    public function getDocumentName(): string
    {
        return $this->documentName;
    }
}