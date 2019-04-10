<?php
namespace CG\Intersoft\RoyalMail\Request\Shipment;

use CG\Intersoft\RoyalMail\Request\PostAbstract;
use CG\Intersoft\RoyalMail\Response\Shipment\Cancel as Response;
use SimpleXMLElement;

class Cancel extends PostAbstract
{
    static $requestNameSpace = 'cancelShipmentRequest';

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
        return 'shipments/cancelShipmentRequest';
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
        $cancel = $xml->addChild('cancel');
        $cancel->addChild('trackingNumber', $this->getTrackingNumber());
        return $xml;
    }
}