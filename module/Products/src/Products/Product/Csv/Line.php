<?php
namespace Products\Product\Csv;

class Line
{
    /** @var string|null */
    protected $name;
    /** @var string|null */
    protected $description;
    /** @var string|null */
    protected $condition;
    protected $price;
    /** @var string|null */
    protected $image;
    /** @var string|null */
    protected $stock;

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'condition' => $this->getCondition(),
            'price' => $this->getPrice(),
            'image' => $this->getImage(),
            'stock' => $this->getStock()
        ];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }
}
