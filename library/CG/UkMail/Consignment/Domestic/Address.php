<?php
namespace CG\UkMail\Consignment\Domestic;

class Address
{
    /** @var string|null */
    protected $businessName;
    /** @var string */
    protected $address1;
    /** @var string|null */
    protected $address2;
    /** @var string|null */
    protected $address3;
    /** @var string */
    protected $postalTown;
    /** @var string|null */
    protected $county;
    /** @var string */
    protected $postcode;
    /** @var string */
    protected $countryCode;
    /** @var string */
    protected $addressType;
    /** @var string|null */
    protected $servicePointId;

    public function __construct(
        ?string $businessName,
        string $address1,
        ?string $address2,
        ?string $address3,
        string $postalTown,
        ?string $county,
        string $postcode,
        string $countryCode,
        string $addressType,
        ?string $servicePointId = null
    ) {
        $this->businessName = $businessName;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->address3 = $address3;
        $this->postalTown = $postalTown;
        $this->county = $county;
        $this->postcode = $postcode;
        $this->countryCode = $countryCode;
        $this->addressType = $addressType;
        $this->servicePointId = $servicePointId;
    }

    public function toArray(): array
    {
        return [
            'businessName' => $this->getBusinessName(),
            'address1' => $this->getAddress1(),
            'address2' => $this->getAddress2(),
            'address3' => $this->getAddress3(),
            'postalTown' => $this->getPostalTown(),
            'county' => $this->getCounty(),
            'postcode' => $this->getPostcode(),
            'countryCode' => $this->getCountryCode(),
            'addressType' => $this->getAddressType(),
            'servicePointId' => $this->getServicePointId(),
        ];
    }

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function getPostalTown(): string
    {
        return $this->postalTown;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getAddressType(): string
    {
        return $this->addressType;
    }

    public function getServicePointId(): ?string
    {
        return $this->servicePointId;
    }
}