<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\EntityTrait\CarrierTrait;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\CarrierServices as Response;

class CarrierServices extends RequestAbstract
{
    use CarrierTrait;

    const METHOD = 'GET';
    const URI = '/carriers';
    const URI_SUFFIX = '/services';

    public function __construct(string $carrierId)
    {
        $this->setCarrierId($carrierId);
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
        return parent::getUri() . '/' . $this->getCarrierId() . static::URI_SUFFIX;
    }
}
