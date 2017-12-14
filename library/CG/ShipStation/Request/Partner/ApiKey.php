<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\Messages\Account;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\ApiKey as Response;

class ApiKey extends PartnerRequestAbstract
{
    /** @var Account */
    protected $account;

    const METHOD = 'POST';
    const URI = '/accounts';
    const URI_SUFFIX = '/api_keys';

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function toArray(): array
    {
        return [
            'description' => 'Partner Access Key'
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getUri(): string
    {
        return parent::getUri() . '/' . $this->account->getAccountId() .  static::URI_SUFFIX;
    }
}
