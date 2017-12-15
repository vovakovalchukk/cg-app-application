<?php
namespace CG\ShipStation\Messages;

class ShipmentAddress extends Address
{
    /** @var string */
    protected $companyName;
    /** @var bool */
    protected $addressResidentialIndicator;

    public function __construct(
        string $name,
        string $phone,
        string $addressLine1,
        string $cityLocality,
        string $province,
        string $postalCode,
        string $countryCode,
        string $addressLine2 = '',
        string $email = '',
        string $companyName = '',
        string $addressResidentialIndicator = ''
    ) {
        parent::__construct(
            $name,
            $phone,
            $addressLine1,
            $cityLocality,
            $province,
            $postalCode,
            $countryCode,
            $addressLine2,
            $email
        );
        $this->companyName = $companyName;
        $this->addressResidentialIndicator = $addressResidentialIndicator;
    }

    public static function build($decodedJson): ShipmentAddress
    {
        $noEmail = '';
        return new static(
            $decodedJson->name,
            $decodedJson->phone,
            $decodedJson->address_line1,
            $decodedJson->city_locality,
            $decodedJson->state_province,
            $decodedJson->postal_code,
            $decodedJson->country_code,
            $decodedJson->address_line2 ?? '',
            $decodedJson->address_line2 ?? '',
            $noEmail,
            $decodedJson->company_name ?? '',
            $decodedJson->address_residential_indicator ?? ''
        );
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): ShipmentAddress
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function isAddressResidentialIndicator(): string
    {
        return $this->addressResidentialIndicator;
    }

    /**
     * @return self
     */
    public function setAddressResidentialIndicator(string $addressResidentialIndicator): ShipmentAddress
    {
        $this->addressResidentialIndicator = $addressResidentialIndicator;
        return $this;
    }
}