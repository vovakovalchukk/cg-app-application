<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\Messages\Account;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\Account as Response;

class GetAccountByExternalId extends PartnerRequestAbstract
{
    const METHOD = 'GET';
    const URI = '/accounts/external_account_id';

    /** @var Account */
    protected $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public static function buildFromExternalAccountId(int $accountId)
    {
        return (new static(new Account($accountId)));
    }

    public function toArray(): array
    {
        return [];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->getAccount()->getAccountId();
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }
}
