<?php
namespace CG\ShipStation\Request\Shipping\Manifest;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Create as Response;
use CG\Stdlib\Date;
use CG\Stdlib\DateTime;

class Query extends RequestAbstract
{
    const URI = '/manifests';

    const MAX_PAGE_SIZE = 500;

    /** @var string */
    protected $warehouseId;
    /** @var string */
    protected $carrierId;
    /** @var DateTime */
    protected $shipDateStart;
    /** @var DateTime */
    protected $shipDateEnd;
    /** @var Datetime */
    protected $createdAtStart;
    /** @var DateTime */
    protected $createdAtEnd;
    /** @var integer */
    protected $page;
    /** @var integer */
    protected $pageSize;

    public function __construct(
        string $warehouseId,
        ?string $carrierId = null,
        ?DateTime $shipDateStart = null,
        ?DateTime $shipDateEnd = null,
        ?DateTime $createdAtStart = null,
        ?DateTime $createdAtEnd = null,
        ?int $page = null,
        ?int $pageSize = null
    ) {
        $this->carrierId = $carrierId;
        $this->warehouseId = $warehouseId;
        $this->shipDateStart = $shipDateStart;
        $this->shipDateEnd = $shipDateEnd;
        $this->createdAtStart = $createdAtStart;
        $this->createdAtEnd = $createdAtEnd;
        $this->page = $page;
        $this->pageSize = $pageSize;
    }

    public function toArray(): array
    {
        return [];
    }

    public function getQueryParams(): array
    {
        return [
            'warehouse_id' => $this->getWarehouseId(),
            'carrier_id' => $this->getCarrierId(),
            'ship_date_start' => $this->getShipDateStart() ? $this->getShipDateStart()->format('c') : null,
            'ship_date_end' => $this->getShipDateEnd() ? $this->getShipDateEnd()->format('c') : null,
            'created_at_start' => $this->getCreatedAtStart() ? $this->getCreatedAtStart()->format('c') : null,
            'created_at_end' => $this->getCreatedAtEnd() ? $this->getCreatedAtEnd()->format('c') : null,
            'page' => $this->getPage(),
            'page_size' => $this->getPageSize()
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function getCarrierId(): ?string
    {
        return $this->carrierId;
    }

    public function getShipDateStart(): ?DateTime
    {
        return $this->shipDateStart;
    }

    public function getShipDateEnd(): ?DateTime
    {
        return $this->shipDateEnd;
    }

    public function getCreatedAtStart(): ?DateTime
    {
        return $this->createdAtStart;
    }

    public function getCreatedAtEnd(): ?DateTime
    {
        return $this->getCreatedAtEnd;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }
}