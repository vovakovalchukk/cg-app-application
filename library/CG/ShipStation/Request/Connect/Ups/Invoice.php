<?php
namespace CG\ShipStation\Request\Connect\Ups;

class Invoice
{
    /** @var string */
    protected $controlId;
    /** @var string */
    protected $invoiceNumber;
    /** @var float */
    protected $invoiceAmount;
    /** @var string */
    protected $invoiceDate;

    public function __construct(string $invoiceNumber, float $invoiceAmount, string $invoiceDate, ?string $controlId)
    {
        $this->controlId = $controlId;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceAmount = $invoiceAmount;
        $this->invoiceDate = $invoiceDate;
    }

    public static function fromArray(array $array): Invoice
    {
        return new static(
            $array['invoice number'] ?? $array['invoice_number'],
            $array['invoice amount'] ?? $array['invoice_amount'],
            $array['invoice date'] ?? $array['invoice_date'],
            $array['control id'] ?? null

        );
    }

    public function toArray(): array
    {
        $returnData = [
            'invoice_number' => $this->getInvoiceNumber(),
            'invoice_amount' => $this->getInvoiceAmount(),
            'invoice_date' => $this->getInvoiceDate(),
        ];
        if (isset($this->controlId)) {
            $returnData['control_id'] = $this->getControlId();
        }
        return $returnData;
    }

    public function getControlId(): ?string
    {
        return $this->controlId;
    }

    public function setControlId(?string $controlId): Invoice
    {
        $this->controlId = $controlId;
        return $this;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): Invoice
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceAmount(): float
    {
        return $this->invoiceAmount;
    }

    public function setInvoiceAmount(float $invoiceAmount): Invoice
    {
        $this->invoiceAmount = $invoiceAmount;
        return $this;
    }

    public function getInvoiceDate(): string
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(string $invoiceDate): Invoice
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }
}