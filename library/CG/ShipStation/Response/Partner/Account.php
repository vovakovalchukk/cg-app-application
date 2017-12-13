<?php
namespace CG\ShipStation\Response\Partner;

use CG\ShipStation\EntityTrait\AccountTrait;
use CG\ShipStation\EntityTrait\TimestampableTrait;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class Account extends ResponseAbstract
{
    use TimestampableTrait;
    use AccountTrait;

    /** @var  string */
    protected $externalAccountId;
    /** @var  bool */
    protected $active;

    protected function build($decodedJson): self
    {
        return $this->setAccountId($decodedJson->account_id)
            ->setExternalAccountId($decodedJson->external_account_id)
            ->setCreatedAt(new DateTime($decodedJson->created_at))
            ->setModifiedAt(new DateTime($decodedJson->modified_at))
            ->setActive($decodedJson->active);
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
}
