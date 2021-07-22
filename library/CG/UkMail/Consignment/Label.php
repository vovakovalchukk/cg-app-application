<?php
namespace CG\UkMail\Consignment;

class Label
{
    /** @var string */
    protected $label;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}