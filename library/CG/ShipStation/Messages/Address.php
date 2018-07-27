<?php
namespace CG\ShipStation\Messages;

class Address
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

    public function __construct(
        string $name,
        string $phone,
        string $addressLine1,
        string $cityLocality,
        string $province,
        string $postalCode,
        string $countryCode,
        string $addressLine2 = '',
        string $email = ''
    ) {
        $this->name = $name;
        $this->phone = $phone;
        $this->addressLine1 = $addressLine1;
        $this->cityLocality = $cityLocality;
        $this->province = $province;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
        $this->addressLine2 = $addressLine2;
        $this->email = $email;
    }

    public static function fromArray(array $array): Address
    {
        return new static(
            $array['company'],
            $array['phone'],
            $array['address1'],
            $array['city'],
            $array['state'],
            $array['postal_code'] ?? $array['postal code'],
            $array['country_code'] ?? $array['country code'],
            $array['address2'],
            $array['email']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'address_line1' => $this->getAddressLine1(),
            'address_line2' => $this->getAddressLine2(),
            'city_locality' => $this->getCityLocality(),
            'state_province' => $this->getProvince(),
            'postal_code' => $this->getPostalCode(),
            'country_code' => $this->getCountryCode(),
            'email' => $this->getEmail(),
        ];
    }

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
