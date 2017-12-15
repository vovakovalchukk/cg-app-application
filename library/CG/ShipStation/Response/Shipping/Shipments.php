<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\ResponseAbstract;
use Countable;
use Iterator;

class Shipments extends ResponseAbstract implements Countable, Iterator
{
    protected $hasErrors;
    protected $shipments;

    public function __construct(bool $hasErrors, Shipment ...$shipments)
    {
        $this->hasErrors = $hasErrors;
        $this->shipments = $shipments;
    }

    protected static function build($decodedJson): Shipments
    {
        $shipments = [];
        foreach ($decodedJson->shipments as $shipmentJson) {
            $shipments[] = Shipment::build($shipmentJson);
        }

        return new static(
            $decodedJson->has_errors,
            ...$shipments
        );
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    public function getShipments(): array
    {
        return $this->shipments;
    }

    /* Iterator methods */
    public function current()
    {
        return current($this->shipments);
    }

    public function next()
    {
        return next($this->shipments);
    }

    public function key()
    {
        return key($this->shipments);
    }

    public function valid()
    {
        $key = $this->key();
        return isset($this->shipments[$key]);
    }

    public function rewind()
    {
        reset($this->shipments);
    }

    /* Countable methods */
    public function count()
    {
        return count($this->shipments);
    }
}