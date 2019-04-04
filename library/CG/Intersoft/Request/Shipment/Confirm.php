<?php
namespace CG\Intersoft\Request\Shipment;

use CG\Intersoft\Response\Shipment\Confirm as Response;
use CG\Intersoft\RoyalMail\Request\PostAbstract;
use SimpleXMLElement;

class Confirm extends PostAbstract
{
    /** @var string */
    protected $carrierCode;

    public function __construct(string $carrierCode)
    {
        $this->carrierCode = $carrierCode;
    }

    public function getUri(): string
    {
        return 'shipments/confirmShipmentRequest';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function asXml(): string
    {
        $xml = new SimpleXMLElement("<confirmShipmentRequest></confirmShipmentRequest>");
        $xml = $this->addIntegrationHeader($xml);
        $xml = $this->addCarrierCode($xml);
        return $xml->asXML();
    }

    protected function addCarrierCode(SimpleXMLElement $xml): SimpleXMLElement
    {
        $carrierCodes = $xml->addChild('carrierCodes');
        $carrierCodes->addChild('carrierCode', $this->carrierCode);
        return $xml;
    }
}