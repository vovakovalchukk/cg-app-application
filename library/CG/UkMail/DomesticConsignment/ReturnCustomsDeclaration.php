<?php
namespace CG\UkMail\DomesticConsignment;

class ReturnCustomsDeclaration
{
    /** @var string */
    protected $descriptionOfGoods;
    /** @var float */
    protected $totalValue;
    /** @var string */
    protected $currencyCode;

    public function __construct(string $descriptionOfGoods, float $totalValue, string $currencyCode)
    {
        $this->descriptionOfGoods = $descriptionOfGoods;
        $this->totalValue = $totalValue;
        $this->currencyCode = $currencyCode;
    }

    public function toArray(): array
    {
        return [
            'descriptionOfGoods' => $this->getDescriptionOfGoods(),
            'totalValue' => $this->getTotalValue(),
            'currencyCode' => $this->getCurrencyCode()
        ];
    }

    public function getDescriptionOfGoods(): string
    {
        return $this->descriptionOfGoods;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}