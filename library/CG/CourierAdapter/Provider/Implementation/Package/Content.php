<?php
namespace CG\CourierAdapter\Provider\Implementation\Package;

use CG\CourierAdapter\Package\ContentInterface;

class Content implements ContentInterface
{
    protected $description;
    protected $hsCode;
    protected $hsCodeDescription;
    protected $origin;
    protected $quantity;
    protected $weight;
    protected $unitValue;
    protected $unitCurrency;
    protected $name;
    protected $composition;
    protected $sku;

    public function __construct(
        $description,
        $hsCode,
        $hsCodeDescription,
        $origin,
        $quantity,
        $weight,
        $unitValue,
        $unitCurrency,
        $name,
        $composition,
        $sku
    ) {
        $this->setDescription($description)
            ->setHsCode($hsCode)
            ->setHsCodeDescription($hsCodeDescription)
            ->setOrigin($origin)
            ->setQuantity($quantity)
            ->setWeight($weight)
            ->setUnitValue($unitValue)
            ->setUnitCurrency($unitCurrency)
            ->setName($name)
            ->setComposition($composition)
            ->setSku($sku);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getHsCode()
    {
        return $this->hsCode;
    }

    public function getHsCodeDescription(): string
    {
        return $this->hsCodeDescription;
    }

    public function setHsCodeDescription(string $hsCodeDescription): Content
    {
        $this->hsCodeDescription = $hsCodeDescription;
        return $this;
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getComposition()
    {
        return $this->composition;
    }

    public function setComposition($composition)
    {
        $this->composition = $composition;
        return $this;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }
}