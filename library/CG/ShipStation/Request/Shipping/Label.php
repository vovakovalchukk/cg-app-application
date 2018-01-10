<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Label as Response;

class Label extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/labels/shipment';

    const FORMAT_PDF = 'pdf';

    /** @var string */
    protected $shipmentId;
    /** @var string */
    protected $format;
    /** @var bool */
    protected $testLabel;

    public function __construct(string $shipmentId, ?string $format = null, bool $testLabel = false)
    {
        $this->shipmentId = $shipmentId;
        $this->format = $format ?? static::FORMAT_PDF;
        $this->testLabel = $testLabel;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->shipmentId;
    }

    public function toArray(): array
    {
        return [
            'label_format' => $this->format,
            'test_label' => $this->testLabel
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}