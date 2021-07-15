<?php
namespace CG\UkMail\Authenticate;

class Account
{
//[accountNumber] => I090559
//[status] => ExemptFromStop
//[type] => Domestic
//[companyName] => FUNKY CHALK LIMITED
//[tradingAddress] => FUNKY CHALK LIMITED, FUNKY CHALK LIMITED, SPRING VALLEY MILLS, STANNINGLEY, LS28 6DW, WEST YORKSHIRE
//[region] => England
//[postcode] => LS28 6DW
//[contactNumber] => 0113 2558877
//[VATNumber] => GB123456789
//[customerRefAlias] => Customer Reference
//[alternativeRefAlias] => Alternative Reference

    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $status;
    /** @var string */
    protected $type;
    /** @var string */
    protected $companyName;
    /** @var string */
    protected $tradingAddress;
    /** @var string */
    protected $region;
    /** @var string */
    protected $postcode;
    /** @var string */
    protected $contactNumber;
    /** @var string */
    protected $vatNumber;
    /** @var string */
    protected $customerRefAlias;
    /** @var string */
    protected $alternativeRefAlias;

    public function __construct(
        $accountNumber,
        $status,
        $type,
        $companyName,
        $tradingAddress,
        $region,
        $postcode,
        $contactNumber,
        $vatNumber,
        $customerRefAlias,
        $alternativeRefAlias
    ) {
        $this->accountNumber = $accountNumber;
        $this->status = $status;
        $this->type = $type;
        $this->companyName = $companyName;
        $this->tradingAddress = $tradingAddress;
        $this->region = $region;
        $this->postcode = $postcode;
        $this->contactNumber = $contactNumber;
        $this->vatNumber = $vatNumber;
        $this->customerRefAlias = $customerRefAlias;
        $this->alternativeRefAlias = $alternativeRefAlias;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getTradingAddress(): string
    {
        return $this->tradingAddress;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function getContactNumber(): string
    {
        return $this->contactNumber;
    }

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    public function getCustomerRefAlias(): string
    {
        return $this->customerRefAlias;
    }

    public function getAlternativeRefAlias(): string
    {
        return $this->alternativeRefAlias;
    }
}