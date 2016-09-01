<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\CourierAdapter\AddressInterface;

class Address implements AddressInterface
{
    protected $companyName;
    protected $firstName;
    protected $lastName;
    protected $line1;
    protected $line2;
    protected $line3;
    protected $line4;
    protected $postCode;
    protected $country;
    protected $ISOAlpha2CountryCode;
    protected $emailAddress;
    protected $phoneNumber;

    public function __construct(
        $companyName,
        $firstName,
        $lastName,
        $line1,
        $line2,
        $line3,
        $line4,
        $postCode,
        $country,
        $ISOAlpha2CountryCode,
        $emailAddress,
        $phoneNumber
    ) {
        $this->setCompanyName($companyName)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setLine1($line1)
            ->setLine2($line2)
            ->setLine3($line3)
            ->setLine4($line4)
            ->setPostCode($postCode)
            ->setCountry($country)
            ->setISOAlpha2CountryCode($ISOAlpha2CountryCode)
            ->setEmailAddress($emailAddress)
            ->setPhoneNumber($phoneNumber);
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getLine1()
    {
        return $this->line1;
    }

    public function getLine2()
    {
        return $this->line2;
    }

    public function getLine3()
    {
        return $this->line3;
    }

    public function getLine4()
    {
        return $this->line4;
    }

    public function getPostCode()
    {
        return $this->postCode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getISOAlpha2CountryCode()
    {
        return $this->ISOAlpha2CountryCode;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setLine1($line1)
    {
        $this->line1 = $line1;
        return $this;
    }

    public function setLine2($line2)
    {
        $this->line2 = $line2;
        return $this;
    }

    public function setLine3($line3)
    {
        $this->line3 = $line3;
        return $this;
    }

    public function setLine4($line4)
    {
        $this->line4 = $line4;
        return $this;
    }

    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
        return $this;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function setISOAlpha2CountryCode($ISOAlpha2CountryCode)
    {
        $this->ISOAlpha2CountryCode = $ISOAlpha2CountryCode;
        return $this;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
}
