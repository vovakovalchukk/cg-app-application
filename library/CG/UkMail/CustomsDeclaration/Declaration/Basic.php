<?php
namespace CG\UkMail\CustomsDeclaration\Declaration;

use CG\UkMail\CustomsDeclaration\CustomsDeclarationInterface;

class Basic implements CustomsDeclarationInterface
{
    /** @var string */
    protected $invoiceType;
    /** @var string */
    protected $descriptionOfGoods;
    /** @var bool */
    protected $documentsOnly;
    /** @var float */
    protected $totalValue;
    /** @var string */
    protected $currencyCode;

    public function __construct(
        string $invoiceType,
        string $descriptionOfGoods,
        bool $documentsOnly,
        float $totalValue,
        string $currencyCode
    ) {
        $this->invoiceType = $invoiceType;
        $this->descriptionOfGoods = $descriptionOfGoods;
        $this->documentsOnly = $documentsOnly;
        $this->totalValue = $totalValue;
        $this->currencyCode = $currencyCode;
    }

    public static function fromArray(array $array): CustomsDeclarationInterface
    {
        return new static(
            $array['invoiceType'],
            $array['descriptionOfGoods'],
            $array['documentsOnly'],
            $array['totalValue'],
            $array['currencyCode']
        );
    }

    public function toArray(): array
    {
        return [
            'basic' => [
                'invoiceType' => $this->getInvoiceType(),
                'descriptionOfGoods' => $this->getDescriptionOfGoods(),
                'documentsOnly' => $this->isDocumentsOnly(),
                'totalValue' => $this->getTotalValue(),
                'currencyCode' => $this->getCurrencyCode(),
            ]
        ];
    }

    public function getInvoiceType(): string
    {
        return $this->invoiceType;
    }

    public function getDescriptionOfGoods(): string
    {
        return $this->descriptionOfGoods;
    }

    public function isDocumentsOnly(): bool
    {
        return $this->documentsOnly;
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