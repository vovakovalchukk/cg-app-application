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
        foreach ($shipments as $shipment) {
            $this->shipments[$shipment->getShipmentId()] = $shipment;
        }
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

    public function getShipmentById(string $id): Shipment
    {
        if (!isset($this->shipments[$id])) {
            throw new \InvalidArgumentException('Shipment with ID ' . $id . ' not in array of shipments');
        }
        return $this->shipments[$id];
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
        return isset($this->shipments[$this->key()]);
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