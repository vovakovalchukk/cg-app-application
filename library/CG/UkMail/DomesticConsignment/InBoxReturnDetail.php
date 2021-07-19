<?php
namespace CG\UkMail\DomesticConsignment;

class InBoxReturnDetail
{
    /** @var string */
    protected $returnAccountNumber;
    /** @var string|null */
    protected $returnCustomerReference;
    /** @var ReturnCustomsDeclaration|null */
    protected $returnCustomsDeclaration;

    public function __construct(
        string $returnAccountNumber,
        ?string$returnCustomerReference,
        ?ReturnCustomsDeclaration $returnCustomsDeclaration
    ) {
        $this->returnAccountNumber = $returnAccountNumber;
        $this->returnCustomerReference = $returnCustomerReference;
        $this->returnCustomsDeclaration = $this->returnCustomsDeclaration;
    }

    public function toArray(): array
    {
        return [
            'returnAccountNumber' => $this->getReturnAccountNumber(),
            'returnCustomerReference' => $this->getReturnCustomerReference(),
            'returnCustomsDeclaration' => $this->getReturnCustomsDeclaration()->toArray(),
        ];
    }

    public function getReturnAccountNumber(): string
    {
        return $this->returnAccountNumber;
    }

    public function getReturnCustomerReference(): ?string
    {
        return $this->returnCustomerReference;
    }

    public function getReturnCustomsDeclaration(): ?ReturnCustomsDeclaration
    {
        return $this->returnCustomsDeclaration;
    }
}