<?php
namespace CG\ShipStation\Entity;

class ShipmentAddress extends Address
{
    /** @var string */
    protected $companyName;
    /** @var string */
    protected $stateProvince;
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
        string $stateProvince = '',
        ?bool $addressResidentialIndicator = null
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
        $this->stateProvince = $stateProvince;
        $this->addressResidentialIndicator = $addressResidentialIndicator;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @return self
     */
    public function setCompanyName(string $companyName)
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getStateProvince(): string
    {
        return $this->stateProvince;
    }

    /**
     * @return self
     */
    public function setStateProvince(string $stateProvince)
    {
        $this->stateProvince = $stateProvince;
        return $this;
    }

    public function isAddressResidentialIndicator(): ?bool
    {
        return $this->addressResidentialIndicator;
    }

    /**
     * @return self
     */
    public function setAddressResidentialIndicator(?bool $addressResidentialIndicator)
    {
        $this->addressResidentialIndicator = $addressResidentialIndicator;
        return $this;
    }
}