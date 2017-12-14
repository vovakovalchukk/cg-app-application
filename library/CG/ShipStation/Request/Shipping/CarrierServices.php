<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\Entity\Carrier;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\CarrierServices as Response;

class CarrierServices extends RequestAbstract
{

    const METHOD = 'GET';
    const URI = '/carriers';
    const URI_SUFFIX = '/services';

    /** @var  Carrier */
    protected $carrier;

    public function __construct(Carrier $carrier)
    {
        $this->carrier = $carrier;
    }

    public function toArray(): array
    {
        return [];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->carrier->getCarrierId() . static::URI_SUFFIX;
    }
}
