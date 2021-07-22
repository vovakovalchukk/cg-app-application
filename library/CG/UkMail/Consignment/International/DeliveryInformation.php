<?php
namespace CG\UkMail\Consignment\International;

class DeliveryInformation
{
    /** @var string */
    protected $localContactName;
    /** @var string */
    protected $localContactNumber;
    /** @var string */
    protected $contactNumberType;
    /** @var string */
    protected $localContactEmail;
    /** @var Address[] */
    protected $deliveryAddresses;

    public function __construct(
        string $localContactName,
        string $localContactNumber,
        string $contactNumberType,
        string $localContactEmail,
        array $deliveryAddresses
    ) {
        $this->localContactName = $localContactName;
        $this->localContactNumber = $localContactNumber;
        $this->contactNumberType = $contactNumberType;
        $this->localContactEmail = $localContactEmail;
        $this->deliveryAddresses = $deliveryAddresses;
    }

    public function toArray(): array
    {
        $deliveryInfo = [
            'localContactName' => $this->getLocalContactName(),
            'localContactNumber' => $this->getLocalContactNumber(),
            'contactNumberType' => $this->getContactNumberType(),
            'localContactEmail' => $this->getLocalContactEmail(),
        ];

        /** @var Address $address */
        foreach ($this->getDeliveryAddresses() as $address) {
            $deliveryInfo['deliveryAddresses'][] = $address->toArray();
        }

        return $deliveryInfo;
    }

    public function getLocalContactName(): string
    {
        return $this->localContactName;
    }

    public function getLocalContactNumber(): string
    {
        return $this->localContactNumber;
    }

    public function getContactNumberType(): string
    {
        return $this->contactNumberType;
    }

    public function getLocalContactEmail(): string
    {
        return $this->localContactEmail;
    }

    /**
     * @return Address[]
     */
    public function getDeliveryAddresses(): array
    {
        return $this->deliveryAddresses;
    }
}