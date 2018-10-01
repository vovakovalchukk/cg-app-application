<?php
namespace CG\ShipStation\Messages;

class Account
{
    /** @var  string */
    protected $accountId;

    public function __construct(string $accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }
}
