<?php
namespace CG\UkMail\Consignment\Domestic;

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
    /** @var string|null */
    protected $specialInstructions;
    /** @var string|null */
    protected $secureLocation;

    public function __construct(
        string $localContactName,
        string $localContactNumber,
        string $contactNumberType,
        string $localContactEmail,
        array $deliveryAddresses,
        ?string $specialInstructions = null,
        ?string $secureLocation = null
    ) {
        $this->localContactName = $localContactName;
        $this->localContactNumber = $localContactNumber;
        $this->contactNumberType = $contactNumberType;
        $this->localContactEmail = $localContactEmail;
        $this->deliveryAddresses = $deliveryAddresses;
        $this->specialInstructions = $specialInstructions;
        $this->secureLocation = $secureLocation;
    }

    public function toArray(): array
    {
        $deliveryInfo = [
            'localContactName' => $this->getLocalContactName(),
            'localContactNumber' => $this->getLocalContactNumber(),
            'contactNumberType' => $this->getContactNumberType(),
            'localContactEmail' => $this->getLocalContactEmail(),
            'specialInstructions' => $this->getSpecialInstructions(),
            'secureLocation' => $this->getSecureLocation(),
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

    public function getSpecialInstructions(): ?string
    {
        return $this->specialInstructions;
    }

    public function getSecureLocation(): ?string
    {
        return $this->secureLocation;
    }
}