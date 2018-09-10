<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\Messages\ConnectAddress;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Connect\Response;

class RoyalMail extends RequestAbstract implements ConnectInterface
{
    const METHOD = 'POST';
    const URI = '/connections/carriers/royal_mail';

    /** @var string */
    protected $nickname;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $obaEmail;
    /** @var string */
    protected $contactName;
    /** @var ConnectAddress */
    protected $address;

    public function __construct(
        string $nickname,
        string $accountNumber,
        string $obaEmail,
        string $contactName,
        ConnectAddress $address
    ) {
        $this->nickname = $nickname;
        $this->accountNumber = $accountNumber;
        $this->obaEmail = $obaEmail;
        $this->contactName = $contactName;
        $this->address = $address;
    }

    public static function fromArray(array $params): ConnectInterface
    {
        $params['address1'] = $params['street line1'] ?? $params['street_line1'];
        $params['address2'] = $params['street line2'] ?? $params['street_line2'];
        $params['state'] = '';
        $params['company'] = '';
        $params['country_code'] = 'GB';

        return new self(
            $params['nickname'],
            $params['account number'] ?? $params['account_number'],
            $params['oba email'] ?? $params['oba_email'],
            $params['contact name'] ?? $params['contact_name'],
            ConnectAddress::fromArray($params)
        );
    }

    public function toArray(): array
    {
        return [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'oba_email' => $this->getObaEmail(),
            'contact_name' => $this->getContactName(),
            'email' => $this->getAddress()->getEmail(),
            'street_line1' => $this->getAddress()->getAddressLine1(),
            'street_line2' => $this->getAddress()->getAddressLine2(),
            'city' => $this->getAddress()->getCityLocality(),
            'postal_code' => $this->getAddress()->getPostalCode(),
            'phone' => $this->getAddress()->getPhone(),
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

    public function setNickname(string $nickname): RoyalMail
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): RoyalMail
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getObaEmail(): string
    {
        return $this->obaEmail;
    }

    public function setObaEmail(string $obaEmail): RoyalMail
    {
        $this->obaEmail = $obaEmail;
        return $this;
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): RoyalMail
    {
        $this->contactName = $contactName;
        return $this;
    }

    public function getAddress(): ConnectAddress
    {
        return $this->address;
    }

    public function setAddress(ConnectAddress $address): RoyalMail
    {
        $this->address = $address;
        return $this;
    }
}