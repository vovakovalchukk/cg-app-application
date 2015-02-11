<?php
namespace Orders\Order\PickList\Image;

use IteratorAggregate;
use ArrayIterator;

class Map implements IteratorAggregate
{
    protected $skusToUrl = [];
    protected $urlsToSkus = [];
    protected $skusToContents = [];

    public function setContentsForUrl($url, $contents)
    {
        if(!isset($this->urlsToSkus[$url])) {
            return;
        }

        foreach($this->urlsToSkus[$url] as $index => $sku) {
            $this->skusToContents[$sku] = $contents;
        }
    }

    public function getContentsForSku($sku)
    {
        if(!$this->contentExists($sku)) {
            return null;
        }
        return $this->skusToContents[$sku];
    }

    public function setUrlForSku($sku, $url)
    {
        $this->skusToUrl[$sku] = $url;
        $this->urlsToSkus[$url][$sku] = $sku;
    }

    public function getUrlForSku($sku)
    {
        if(!$this->urlExists($sku)){
            return null;
        }
        return $this->skusToUrl[$sku];
    }

    public function contentExists($sku)
    {
        return isset($this->skusToContents[$sku]);
    }

    public function urlExists($sku)
    {
        return isset($this->skusToUrl[$sku]);
    }

    public function unsetSku($sku)
    {
        unset($this->urlsToSkus[$this->skusToUrl[$sku]][$sku]);
        unset($this->skusToUrl[$sku]);
        unset($this->skusToContents[$sku]);
    }

    public function getIterator()
    {

        return new ArrayIterator($this->skusToUrl);
    }
}