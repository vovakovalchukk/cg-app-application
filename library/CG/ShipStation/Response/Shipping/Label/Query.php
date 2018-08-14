<?php
namespace CG\ShipStation\Response\Shipping\Label;

use CG\ShipStation\ResponseAbstract;
use CG\ShipStation\Response\Shipping\Label;

class Query extends ResponseAbstract
{
    /** @var Label[] */
    protected $labels;

    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    protected static function build($decodedJson)
    {
        $labels = [];
        foreach ($decodedJson->labels as $labelJson) {
            $labels = Label::createFromJson($labelJson);
        }
        return new static($labels);
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}