<?php
namespace CG\UkMail\Consignment\Domestic;

class Recipient
{
    /** @var string */
    protected $contactName;
    /** @var string */
    protected $contactEmail;
    /** @var string|null */
    protected $contactNumber;
    /** @var Address */
    protected $recipientAddress;
    /** @var string */
    protected $preDeliveryNotificationType;

    public function __construct(
        string $contactName,
        string $contactEmail,
        ?string $contactNumber,
        Address $recipientAddress,
        string $preDeliveryNotificationType
    ) {
        $this->contactName = $contactName;
        $this->contactEmail = $contactEmail;
        $this->contactNumber = $contactNumber;
        $this->recipientAddress = $recipientAddress;
        $this->preDeliveryNotificationType = $preDeliveryNotificationType;
    }

    public function toArray(): array
    {
        return [
            'contactName' => $this->getContactName(),
            'contactEmail' => $this->getContactEmail(),
            'contactNumber' => $this->getContactNumber(),
            'recipientAddress' => $this->getRecipientAddress()->toArray(),
            'preDeliveryNotificationType' => $this->getPreDeliveryNotificationType()
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

    public function getRecipientAddress(): Address
    {
        return $this->recipientAddress;
    }

    public function getPreDeliveryNotificationType(): string
    {
        return $this->preDeliveryNotificationType;
    }
}