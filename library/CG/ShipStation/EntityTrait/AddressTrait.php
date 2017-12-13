<?php
namespace CG\ShipStation\EntityTrait;

trait AddressTrait
{
    /** @var  string */
    protected $name;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $addressLine1;
    /** @var  string */
    protected $addressLine2;
    /** @var  string */
    protected $cityLocality;
    /** @var  string */
    protected $province;
    /** @var  string */
    protected $postalCode;
    /** @var  string */
    protected $countryCode;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(string $addressLine1)
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(string $addressLine2)
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function getCityLocality(): ?string
    {
        return $this->cityLocality;
    }

    public function setCityLocality(string $cityLocality)
    {
        $this->cityLocality = $cityLocality;
        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province)
    {
        $this->province = $province;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }
}
