<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Connect\Response as Response;

class DhlExpressUk extends RequestAbstract implements ConnectInterface
{
    const METHOD = 'POST';
    const URI = '/connections/carriers/dhl_express_uk';

    /** @var  string */
    protected $nickname;
    /** @var  string */
    protected $accountNumber;
    /** @var  string */
    protected $siteId;
    /** @var  string */
    protected $password;

    public function __construct(string $nickname, string $accountNumber, string $siteId, string $password)
    {
        $this->nickname = $nickname;
        $this->accountNumber = $accountNumber;
        $this->siteId = $siteId;
        $this->password = $password;
    }

    public static function fromArray(array $params): ConnectInterface
    {
        return new static(
            $params['nickname'],
            $params['account number'] ?? $params['account_number'],
            $params['site id'] ?? $params['site_id'],
            $params['password']
        );
    }

    public function toArray(): array
    {
        return [
            'nickname' => $this->getNickname(),
            'account_number' => $this->getAccountNumber(),
            'site_id' => $this->getSiteId(),
            'password' => $this->getPassword(),
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

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getSiteId(): string
    {
        return $this->siteId;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}