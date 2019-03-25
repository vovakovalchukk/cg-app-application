<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\CourierAdapter\LabelInterface;

class Label implements LabelInterface
{
    /** @var string */
    protected $data;
    /** @var string */
    protected $type;

    public function __construct(string $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getType()
    {
        return $this->type;
    }
}