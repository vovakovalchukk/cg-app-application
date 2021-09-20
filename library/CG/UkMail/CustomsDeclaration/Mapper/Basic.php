<?php
namespace CG\UkMail\CustomsDeclaration\Mapper;

use CG\CourierAdapter\Provider\Implementation\Package\Content;
use CG\UkMail\CustomsDeclaration\MapperInterface;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package;

class Basic implements MapperInterface
{
    protected const MAX_LEN = 90;

    protected $descriptionOfGoods;
    protected $totalValue;
    protected $currencyCode;

    public function toArray(Shipment $shipment): array
    {
        $this->parseDataFromContent($shipment);

        return [
            'invoiceType' => MapperInterface::INVOICE_TYPE_PROFORMA,
            'descriptionOfGoods' => $this->getDescriptionOfGoods(),
            'documentsOnly' => false,
            'totalValue' => $this->getTotalValue(),
            'currencyCode' => $this->getCurrencyCode(),
        ];
    }

    protected function parseDataFromContent(Shipment $shipment): void
    {
        $descriptionOfGoods = [];
        $totalValue = 0;
        $currencyCode = '';
        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            /** @var Content $content */
            foreach ($package->getContents() as $content) {
                $descriptionOfGoods[] = $content->getDescription();
                $totalValue += $content->getUnitValue();
                $currencyCode = $content->getUnitCurrency();
            }
        }

        $this->descriptionOfGoods = $descriptionOfGoods;
        $this->totalValue = $totalValue;
        $this->currencyCode = $currencyCode;
    }

    protected function getDescriptionOfGoods(): string
    {
        return substr(implode('|' ,$this->descriptionOfGoods), 0, static::MAX_LEN);
    }

    protected function getTotalValue(): float
    {
        return number_format($this->totalValue, 2);
    }

    protected function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}