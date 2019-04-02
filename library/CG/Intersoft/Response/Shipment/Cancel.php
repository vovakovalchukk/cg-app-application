<?php
namespace CG\Intersoft\RoyalMail\Response\Shipment;

use CG\Intersoft\RoyalMail\Request\PostAbstract;
use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\FromXmlInterface;
use SimpleXMLElement;


class Cancel implements ResponseInterface, FromXmlInterface
{
    /** @var string */
    protected $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public static function fromXml(SimpleXMLElement $xml): Cancel
    {
        return new self(
          $xml->cancelled->trackingNumber
        );
    }
}