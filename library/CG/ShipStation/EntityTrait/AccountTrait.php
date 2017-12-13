<?php
namespace CG\ShipStation\EntityTrait;

trait AccountTrait
{
    /** @var  int */
    protected $accountId;

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
