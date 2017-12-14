<?php
namespace CG\ShipStation\Entity;

class Account
{
    /** @var  int */
    protected $accountId;

    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function setAccountId(int $accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }
}
