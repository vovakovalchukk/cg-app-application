<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\Messages\ConnectAddress as Address;
use CG\ShipStation\Messages\User;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Request\Connect\Ups\Invoice;
use CG\ShipStation\Response\Connect\Response as Response;

class Ups extends RequestAbstract implements ConnectInterface
{
    const METHOD = 'POST';
    const URI = '/connections/carriers/ups';

    /** @var  Address */
    protected $address;
    /** @var  User */
    protected $user;
    /** @var  string */
    protected $nickname;
    /** @var  string */
    protected $accountNumber;
    /** @var  string */
    protected $accountCountryCode;
    /** @var  string */
    protected $accountPostalCode;
    /** @var Invoice */
    protected $invoice;

    public function __construct(
        Address $address,
        User $user,
        string $nickname,
        string $accountNumber,
        string $accountCountryCode,
        string $accountPostalCode,
        Invoice $invoice
    ) {
        $this->address = $address;
        $this->user = $user;
        $this->nickname = $nickname;
        $this->accountNumber = $accountNumber;
        $this->accountCountryCode = $accountCountryCode;
        $this->accountPostalCode = $accountPostalCode;
        $this->invoice = $invoice;
    }

    public static function fromArray(array $params): ConnectInterface
    {
        $address = Address::fromArray($params);
        $user = User::fromArray($params);
        $invoice = Invoice::fromArray($params);

        return new static(
            $address,
            $user,
            $params['nickname'],
            $params['account number'] ?? $params['account_number'],
            $params['account country code'] ?? $params['account_country_code'],
            $params['account postal code'] ?? $params['account_postal_code'],
            $invoice
        );
    }

    public function toArray(): array
    {
        $array = [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'account_country_code' => $this->getAccountCountryCode(),
            'account_postal_code' => $this->getAccountPostalCode(),
            'invoice' => $this->invoice->toArray(),
            'agree_to_technology_agreement' => true
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

    public function getAccountCountryCode(): string
    {
        return $this->accountCountryCode;
    }

    public function setAccountCountryCode(string $accountCountryCode): Ups
    {
        $this->accountCountryCode = $accountCountryCode;
        return $this;
    }

    public function getAccountPostalCode(): string
    {
        return $this->accountPostalCode;
    }

    public function setAccountPostalCode(string $accountPostalCode): Ups
    {
        $this->accountPostalCode = $accountPostalCode;
        return $this;
    }
}
