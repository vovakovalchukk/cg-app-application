<?php
namespace Products\Product\Csv;

use CG\Product\Entity as Product;
use CG\Product\Detail\Entity as Detail;

class Line
{
    /** @var string|null */
    protected $name;
    /** @var string|null */
    protected $description;
    /** @var string|null */
    protected $condition;
    /** @var float|null */
    protected $price;
    /** @var string|null */
    protected $image;
    /** @var string|null */
    protected $stock;
    /** @var string|null */
    protected $sku;
    /** @var string|null */
    protected $ean;
    /** @var string|null */
    protected $asin;
    /** @var string|null */
    protected $brand;
    /** @var string|null */
    protected $mpn;
    /** @var string|null */
    protected $site;
    /** @var string|null */
    protected $category;
    /** @var string|null */
    protected $shipping;
    /** @var string|null */
    protected $location;
    /** @var string|null */
    protected $specifics;

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'condition' => $this->getCondition(),
            'sku' => $this->getSku(),
            'ean' => $this->getEan(),
            'asin' => $this->getAsin(),
            'brand' => $this->getBrand(),
            'mpn' => $this->getMpn(),
            'price' => $this->getPrice(),
            'image' => $this->getImage(),
            'stock' => $this->getStock(),
            'site' => $this->getSite(),
            'category' => $this->getCategory(),
            'shipping' => $this->getShipping(),
            'location' => $this->getLocation(),
            'specifics' => $this->getSpecifics(),
        ];
    }

    public static function createFromProductAndDetails(Product $product, Detail $detail, string $imageUrl, int $stockTotal)
    {
        return (new static)
            ->setName($product->getName())
            ->setSku($product->getSku())
            ->setEan($detail->getEan())
            ->setBrand($detail->getBrand())
            ->setMpn($detail->getMpn())
            ->setAsin($detail->getAsin())
            ->setDescription($detail->getDescription())
            ->setPrice($detail->getPrice())
            ->setCondition($detail->getCondition())
            ->setImage($imageUrl)
            ->setStock($stockTotal);
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

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan($ean)
    {
        $this->ean = $ean;
        return $this;
    }

    public function getAsin(): ?string
    {
        return $this->asin;
    }

    public function setAsin($asin)
    {
        $this->asin = $asin;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn($mpn)
    {
        $this->mpn = $mpn;
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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): Line
    {
        $this->site = $site;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): Line
    {
        $this->category = $category;
        return $this;
    }

    public function getShipping(): ?string
    {
        return $this->shipping;
    }

    public function setShipping(?string $shipping): Line
    {
        $this->shipping = $shipping;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): Line
    {
        $this->location = $location;
        return $this;
    }

    public function getSpecifics(): ?string
    {
        return $this->specifics;
    }

    public function setSpecifics(?string $specifics): Line
    {
        $this->specifics = $specifics;
        return $this;
    }
}
