<?php
namespace CG\UkMail\CustomsDeclaration\Declaration;

use CG\UkMail\CustomsDeclaration\CustomsDeclarationInterface;

class Full implements CustomsDeclarationInterface
{
    /** @var string */
    protected $invoiceType;
    /** @var string */
    protected $invoiceNumber;
    /** @var \DateTime */
    protected $invoiceDate;
    /** @var Article[] */
    protected $articles;
    /** @var int */
    protected $totalArticles;
    /** @var float */
    protected $shippingCharges;
    /** @var float */
    protected $totalValue;
    /** @var string */
    protected $currencyCode;
    /** @var string */
    protected $reasonForExport;
    /** @var string */
    protected $termsOfDelivery;

    public function __construct(
        string $invoiceType,
        string $invoiceNumber,
        \DateTime $invoiceDate,
        array $articles,
        int $totalArticles,
        float $shippingCharges,
        float $totalValue,
        string $currencyCode,
        string $reasonForExport,
        string $termsOfDelivery
    ) {
        $this->invoiceType = $invoiceType;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
        $this->articles = $articles;
        $this->totalArticles = $totalArticles;
        $this->shippingCharges = $shippingCharges;
        $this->totalValue = $totalValue;
        $this->currencyCode = $currencyCode;
        $this->reasonForExport = $reasonForExport;
        $this->termsOfDelivery = $termsOfDelivery;
    }

    public static function fromArray(array $array): CustomsDeclarationInterface
    {
        return new static(
            $array['invoiceType'],
            $array['invoiceNumber'],
            $array['invoiceDate'],
            $array['articles'],
            $array['totalArticles'],
            $array['shippingCharges'],
            $array['totalValue'],
            $array['currencyCode'],
            $array['reasonForExport'],
            $array['termsOfDelivery']
        );
    }

    public function toArray(): array
    {
        $full = [
            'invoiceType' => $this->getInvoiceType(),
            'invoiceNumber' => $this->getInvoiceNumber(),
            'invoiceDate' => $this->getInvoiceDate()->format('Y-m-d'),
            'totalArticles' => $this->getTotalArticles(),
            'shippingCharges' => $this->getShippingCharges(),
            'totalValue' => $this->getTotalValue(),
            'currencyCode' => $this->getCurrencyCode(),
            'reasonforExport' => $this->getReasonForExport(),
            'termsOfDelivery' => $this->getTermsOfDelivery(),
        ];

        /** @var Article $article */
        foreach ($this->getArticles() as $article) {
            $full['articles']['article'][] = $article->toArray();
        }

        return ['full' => $full];
    }

    public function getInvoiceType(): string
    {
        return $this->invoiceType;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceDate(): \DateTime
    {
        return $this->invoiceDate;
    }

    /**
     * @return Article[]
     */
    public function getArticles(): array
    {
        return $this->articles;
    }

    public function getTotalArticles(): int
    {
        return $this->totalArticles;
    }

    public function getShippingCharges(): float
    {
        return $this->shippingCharges;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getReasonForExport(): string
    {
        return $this->reasonForExport;
    }

    public function getTermsOfDelivery(): string
    {
        return $this->termsOfDelivery;
    }
}