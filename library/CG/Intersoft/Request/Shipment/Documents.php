<?php
namespace CG\Intersoft\RoyalMail\Request\Shipment;

use CG\Intersoft\RoyalMail\Response\Shipment\Documents as Response;

use SimpleXMLElement;
use CG\Intersoft\RoyalMail\Request\PostAbstract;

class Documents extends PostAbstract
{
    static $requestNameSpace = 'printDocumentRequest';

    const DOCUMENT_TYPE_CN = 'CN';
    const EXPORT_REASON = 'Sale of goods';

    /** @var string */
    protected $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function getUri(): string
    {
        return 'documents/printDocumentRequest';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function asXml(): string
    {
        $xml = $this->buildXml();
        return $xml->asXml();
    }

    protected function buildXml(): SimpleXMLElement
    {
        $namespace = static::$requestNameSpace;
        $xml = new SimpleXMLElement("<{$namespace}></{$namespace}>");
        $xml = $this->addIntegrationHeader($xml);
        $shipment = $xml->addChild('shipment');
        $shipment->addChild('trackingNumber', $this->getTrackingNumber());
        $shipment->addChild('documentType', static::DOCUMENT_TYPE_CN);
        $shipment->addChild('reasonForExport', static::EXPORT_REASON);
        return $xml;
    }
}