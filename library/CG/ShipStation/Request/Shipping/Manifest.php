<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Manifest as Response;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;

class Manifest extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/v1/manifests';

    const FORMAT_PDF = 'pdf';

    /** @var string */
    protected $carrierId;
    /** @var string */
    protected $warehouseId;
    /** @var DateTime */
    protected $shipDate;
    /** @var array */
    protected $excludedLabelIds;

    public function __construct(string $carrierId, $warehouseId, $shipDate, $excludedLabelIds = [])
    {
        $this->carrierId = $carrierId;
        $this->warehouseId = $warehouseId;
        $this->shipDate = $shipDate;
        $this->excludedLabelIds = $excludedLabelIds;
    }

    public function toArray(): array
    {
        return [
            'carrier_id' => $this->getCarrierId(),
            'warehouse_id' => $this->getWarehouseId(),
            'ship_date' => $this->getShipDate(),
            'excluded_label_ids' => $this->getExcludedLabelIds(),
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getCarrierId(): string
    {
        return $this->getCarrierId();
    }

    public function getWarehouseId(): string
    {
        return $this->getWarehouseId();
    }

    public function getShipDate(): DateTime
    {
        return $this->getShipDate();
    }

    public function getExcludedLabelIds(): array
    {
        return $this->getExcludedLabelIds();
    }
}