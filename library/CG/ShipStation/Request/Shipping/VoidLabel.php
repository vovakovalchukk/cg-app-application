<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\VoidLabel as Response;

class VoidLabel extends RequestAbstract
{
    const METHOD = 'PUT';
    const URI = '/labels';
    const URI_SUFFIX = '/void';

    protected $labelId;

    public function __construct(string $labelId)
    {
        $this->labelId = $labelId;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->labelId . static::URI_SUFFIX;
    }

    public function toArray(): array
    {
        return [];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}