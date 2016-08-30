<?php
namespace CG\CourierAdapter\Provider\Implementation\Package;

use CG\CourierAdapter\Package\ContentInterface;

class Content implements ContentInterface
{
    protected $description;
    protected $hsCode;
    protected $origin;
    protected $quantity;
    protected $weight;
    protected $unitValue;
    protected $unitCurrency;

    public function __construct($description, $hsCode, $origin, $quantity, $weight, $unitValue, $unitCurrency)
    {
        $this->setDescription($description)
            ->setHsCode($hsCode)
            ->setOrigin($origin)
            ->setQuantity($quantity)
            ->setWeight($weight)
            ->setUnitValue($unitValue)
            ->setUnitCurrency($unitCurrency);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getHsCode()
    {
        return $this->hsCode;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getUnitValue()
    {
        return $this->unitValue;
    }

    public function getUnitCurrency()
    {
        return $this->unitCurrency;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setHsCode($hsCode)
    {
        $this->hsCode = $hsCode;
        return $this;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function setUnitValue($unitValue)
    {
        $this->unitValue = $unitValue;
        return $this;
    }

    public function setUnitCurrency($unitCurrency)
    {
        $this->unitCurrency = $unitCurrency;
        return $this;
    }
}