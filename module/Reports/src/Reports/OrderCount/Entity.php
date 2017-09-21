<?php
namespace Reports\OrderCount;

class Entity
{
    protected $unit;
    /** @var array */
    protected $values;

    public function getUnit()
    {
        return $this->unit;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

    public function addValue($key, $value)
    {
        $this->values[$key] = $value;
        return $this;
    }
}
