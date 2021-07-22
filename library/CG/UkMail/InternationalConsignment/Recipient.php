<?php
namespace CG\UkMail\InternationalConsignment;

class Recipient
{
    /** @var string */
    protected $contactName;
    /** @var string */
    protected $contactEmail;
    /** @var string|null */
    protected $contactNumber;
    /** @var string|null */
    protected $taxReference;
    /** @var Address */
    protected $recipientAddress;

    public function __construct(
        string $contactName,
        string $contactEmail,
        ?string $contactNumber,
        ?string $taxReference,
        Address $recipientAddress
    ) {
        $this->contactName = $contactName;
        $this->contactEmail = $contactEmail;
        $this->contactNumber = $contactNumber;
        $this->taxReference = $taxReference;
        $this->recipientAddress = $recipientAddress;
    }

    public function toArray(): array
    {
        return [
            'contactName' => $this->getContactName(),
            'contactEmail' => $this->getContactEmail(),
            'contactNumber' => $this->getContactNumber(),
            'taxReference' => $this->getTaxReference(),
            'recipientAddress' => $this->getRecipientAddress()->toArray(),
        ];
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }

    public function getTaxReference(): ?string
    {
        return $this->taxReference;
    }

    public function getRecipientAddress(): Address
    {
        return $this->recipientAddress;
    }
}