<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\Entity\Address;
use CG\ShipStation\Entity\User;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Connect\Response as Response;

class FedEx extends RequestAbstract implements ConnectInterface
{
    const METHOD = 'POST';
    const URI = '/connections/carriers/fedex';

    /** @var  Address */
    protected $address;
    /** @var  User */
    protected $user;
    /** @var  string */
    protected $nickname;
    /** @var  string */
    protected $accountNumber;

    public function __construct(Address $address, User $user)
    {
        $this->address = $address;
        $this->user = $user;
    }

    public static function fromArray(array $params): ConnectInterface
    {
        $address = new Address(
            $params['company'],
            $params['phone'],
            $params['address1'],
            $params['city'],
            $params['state'],
            $params['postal_code'],
            $params['country_code'],
            $params['address2'],
            $params['email']
        );
        $user = new User($params['first_name'], $params['last_name'], $params['company']);
        return new static($address, $user);
    }

    public function toArray(): array
    {
        return [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'first_name' => $this->user->getFirstName(),
            'last_name' => $this->user->getLastName(),
            'company' => $this->user->getCompanyName(),
            'address1' => $this->address->getAddressLine1(),
            'address2' => $this->address->getAddressLine2(),
            'city' => $this->address->getCityLocality(),
            'state' => $this->address->getProvince(),
            'postal_code' => $this->address->getPostalCode(),
            'country_code' => $this->address->getCountryCode(),
            'email' => $this->address->getEmail(),
            'phone' => $this->address->getPhone(),
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
