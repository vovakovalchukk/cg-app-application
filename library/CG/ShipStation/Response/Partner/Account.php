<?php
namespace CG\ShipStation\Response\Partner;

use CG\ShipStation\Entity\Account as AccountEntity;
use CG\ShipStation\Entity\Timestamp;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class Account extends ResponseAbstract
{
    /** @var  AccountEntity */
    protected $account;
    /** @var  Timestamp */
    protected $timestamp;
    /** @var  string */
    protected $externalAccountId;
    /** @var  bool */
    protected $active;

    public function __construct(AccountEntity $account, Timestamp $timestamp, string $externalAccountId, bool $active)
    {
        $this->account = $account;
        $this->timestamp = $timestamp;
        $this->externalAccountId = $externalAccountId;
        $this->active = $active;
    }

    protected static function build($decodedJson): self
    {
        $account = (new AccountEntity($decodedJson->account_id));
        $timestamp = (new Timestamp(new DateTime($decodedJson->created_at)))
            ->setModifiedAt(new DateTime($decodedJson->modified_at));
        return new static(
            $account,
            $timestamp,
            $decodedJson->external_account_id,
            $decodedJson->active
        );
    }

    public function getExternalAccountId(): string
    {
        return $this->externalAccountId;
    }

    public function setExternalAccountId(string $externalAccountId)
    {
        $this->externalAccountId = $externalAccountId;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
        return $this;
    }

    public function getAccount(): AccountEntity
    {
        return $this->account;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }
}
