<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\Messages\ConnectAddress as Address;
use CG\ShipStation\Messages\User;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Connect\Response as Response;

class Fedex extends RequestAbstract implements ConnectInterface
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

    public function __construct(Address $address, User $user, string $nickname, string $accountNumber)
    {
        $this->address = $address;
        $this->user = $user;
        $this->nickname = $nickname;
        $this->accountNumber = $accountNumber;
    }

    public static function fromArray(array $params): ConnectInterface
    {
        $address = Address::fromArray($params);
        $user = User::fromArray($params);

        return new static($address, $user, $params['nickname'], $params['account number']);
    }

    public function toArray(): array
    {
        $array = [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'agree_to_eula' => true
        ];
        return array_merge($array, $this->user->toArray(), $this->address->toArray());
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
}
