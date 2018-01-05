<?php
namespace Products\Product\Csv;

class File
{
    const HEADERS = ['Name', 'Description', 'Item Condition', 'SKU', 'EAN', 'ASIN', 'Brand', 'MPN' ,'Price', 'Currency', 'Image', 'Stock', 'Site', 'Category', 'Shipping', 'Item location', 'Item Specifics', 'Payment Method', 'Paypal Email'];

    /** @var  Line[] */
    protected $lines;

    /**
     * @return Line[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function setLines(array $lines)
    {
        $this->lines = $lines;
        return $this;
    }

    public function addLine(Line $line)
    {
        $this->lines[] = $line;
    }

    public function getHeadersAsArray()
    {
        return self::HEADERS;
    }

    public function toArray(): array
    {
        $array = [$this->getHeadersAsArray()];
        foreach ($this->getLines() as $line) {
            $array[] = $line->toArray();
        }
        return $array;
    }
}
