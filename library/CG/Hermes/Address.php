<?php
namespace CG\Hermes;

use CG\CourierAdapter\AddressInterface;

class Address implements AddressInterface
{
    /** @var string */
    protected $firstName;
    /** @var string */
    protected $lastName;
    /** @var string */
    protected $line1;
    /** @var string */
    protected $line2;
    /** @var string */
    protected $line3;
    /** @var string */
    protected $line4;
    /** @var string */
    protected $postCode;
    /** @var string */
    protected $emailAddress;
    /** @var string */
    protected $companyName;
    /** @var string */
    protected $country;
    /** @var string */
    protected $isoAlpha2CountryCode;
    /** @var string */
    protected $phoneNumber;

    public function __construct(
        string $firstName,
        string $lastName,
        string $line1,
        string $line2,
        string $line3,
        string $line4,
        string $postCode,
        string $emailAddress,
        string $companyName,
        string $country,
        string $isoAlpha2CountryCode,
        string $phoneNumber
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->line3 = $line3;
        $this->line4 = $line4;
        $this->postCode = $postCode;
        $this->emailAddress = $emailAddress;
        $this->companyName = $companyName;
        $this->country = $country;
        $this->isoAlpha2CountryCode = $isoAlpha2CountryCode;
        $this->phoneNumber = $phoneNumber;
    }

    public static function fromArray(array $array): Address
    {
        return new static(
            $array['firstName'] ?? '',
            $array['lastName'] ?? '',
            $array['line1'] ?? '',
            $array['line2'] ?? '',
            $array['line3'] ?? '',
            $array['line4'] ?? '',
            $array['postCode'] ?? '',
            $array['emailAddress'] ?? '',
            $array['companyName'] ?? '',
            $array['country'] ?? '',
            $array['ISOAlpha2CountryCode'] ?? '',
            $array['phoneNumber'] ?? ''
        );
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getLine1(): string
    {
        return $this->line1;
    }

    public function getLine2(): string
    {
        return $this->line2;
    }

    public function getLine3(): string
    {
        return $this->line3;
    }

    public function getLine4(): string
    {
        return $this->line4;
    }

    public function getPostCode(): string
    {
        return $this->postCode;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getIsoAlpha2CountryCode(): string
    {
        return $this->isoAlpha2CountryCode;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}