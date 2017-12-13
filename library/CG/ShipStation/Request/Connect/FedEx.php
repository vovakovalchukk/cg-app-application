<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\EntityTrait\AddressTrait;
use CG\ShipStation\EntityTrait\UserDetailsTrait;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Connect\FedEx as Response;

class FedEx extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/connections/carriers/fedex';

    use AddressTrait;
    use UserDetailsTrait;

    /** @var  string */
    protected $nickname;
    /** @var  string */
    protected $accountNumber;

    public function __construct(
        string $nickname,
        string $accountNumber,
        string $firstName,
        string $lastName,
        string $address1,
        string $city,
        string $province,
        string $postalCode,
        string $countryCode,
        string $email,
        string $phone,
        string $companyName = '',
        string $address2 = ''
    ) {
        $this->setNickname($nickname)
            ->setAccountNumber($accountNumber)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setAddressLine1($address1)
            ->setCityLocality($city)
            ->setProvince($province)
            ->setPostalCode($postalCode)
            ->setCountryCode($countryCode)
            ->setEmail($email)
            ->setPhone($phone)
            ->setCompanyName($companyName)
            ->setAddressLine2($address2);
    }

    public function toArray(): array
    {
        return [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'company' => $this->getCompanyName(),
            'city' => $this->getCityLocality(),
            'state' => $this->getProvince(),
            'postal_code' => $this->getPostalCode(),
            'country_code' => $this->getCountryCode(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'agree_to_eula' => true
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
}
