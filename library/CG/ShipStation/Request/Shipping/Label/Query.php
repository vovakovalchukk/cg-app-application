<?php
namespace CG\ShipStation\Request\Shipping\Label;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Label\Query as Response;
use DateTime;

class Query extends RequestAbstract
{
    const URI = '/labels';

    /** @var string|null */
    protected $labelStatus;
    /** @var string|null */
    protected $carrierId;
    /** @var string|null */
    protected $serviceCode;
    /** @var string|null */
    protected $trackingNumber;
    /** @var string|null */
    protected $batchId;
    /** @var string|null */
    protected $warehouseId;
    /** @var DateTime|null */
    protected $createdAtStart;
    /** @var DateTime|null */
    protected $createdAtEnd;
    /** @var int|null */
    protected $page;
    /** @var int|null */
    protected $pageSize;
    /** @var string|null */
    protected $sortDir;
    /** @var string|null */
    protected $sortBy;

    public function getUri(): string
    {
        return parent::getUri() . '?' . http_build_query($this->getQueryParams());
    }

    public function toArray(): array
    {
        return [];
    }

    protected function getQueryParams()
    {
        $array = [
            'label_status' => $this->getLabelStatus(),
            'carrier_id' => $this->getCarrierId(),
            'service_code' => $this->getServiceCode(),
            'tracking_number' => $this->getTrackingNumber(),
            'batch_id' => $this->getBatchId(),
            'warehouse_id' => $this->getWarehouseId(),
            'created_at_start' => $this->getCreatedAtStart() ? $this->getCreatedAtStart()->format('c') : null,
            'created_at_end' => $this->getCreatedAtEnd() ? $this->getCreatedAtEnd()->format('c') : null,
            'page' => $this->getPage(),
            'page_size' => $this->getPageSize(),
            'sort_dir' => $this->getSortDir(),
            'sort_by' => $this->getSortBy(),
        ];
        return array_filter($array);
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getLabelStatus(): ?string
    {
        return $this->labelStatus;
    }

    public function setLabelStatus(?string $labelStatus): Query
    {
        $this->labelStatus = $labelStatus;
        return $this;
    }

    public function getCarrierId(): ?string
    {
        return $this->carrierId;
    }

    public function setCarrierId(?string $carrierId): Query
    {
        $this->carrierId = $carrierId;
        return $this;
    }

    public function getServiceCode(): ?string
    {
        return $this->serviceCode;
    }

    public function setServiceCode(?string $serviceCode): Query
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): Query
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function getBatchId(): ?string
    {
        return $this->batchId;
    }

    public function setBatchId(?string $batchId): Query
    {
        $this->batchId = $batchId;
        return $this;
    }

    public function getWarehouseId(): ?string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(?string $warehouseId): Query
    {
        $this->warehouseId = $warehouseId;
        return $this;
    }

    public function getCreatedAtStart(): ?DateTime
    {
        return $this->createdAtStart;
    }

    public function setCreatedAtStart(?DateTime $createdAtStart): Query
    {
        $this->createdAtStart = $createdAtStart;
        return $this;
    }

    public function getCreatedAtEnd(): ?DateTime
    {
        return $this->createdAtEnd;
    }

    public function setCreatedAtEnd(?DateTime $createdAtEnd): Query
    {
        $this->createdAtEnd = $createdAtEnd;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): Query
    {
        $this->page = $page;
        return $this;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(?int $pageSize): Query
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function getSortDir(): ?string
    {
        return $this->sortDir;
    }

    public function setSortDir(?string $sortDir): Query
    {
        $this->sortDir = $sortDir;
        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy): Query
    {
        $this->sortBy = $sortBy;
        return $this;
    }
}