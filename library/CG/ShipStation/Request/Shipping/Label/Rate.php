<?php
namespace CG\ShipStation\Request\Shipping\Label;

use CG\ShipStation\Request\Shipping\Label as LabelRequest;

class Rate extends LabelRequest
{
    const URI = '/labels/rates';

    /** @var string */
    protected $rateId;

    public function __construct(string $rateId, ?string $format = null, bool $testLabel = false)
    {
        $this->rateId = $rateId;
        $this->format = $format ?? static::FORMAT_PDF;
        $this->testLabel = $testLabel;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->rateId;
    }
}