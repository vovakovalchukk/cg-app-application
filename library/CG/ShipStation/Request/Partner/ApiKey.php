<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\EntityTrait\AccountTrait;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\ApiKey as Response;

class ApiKey extends PartnerRequestAbstract
{
    use AccountTrait;

    const METHOD = 'POST';
    const URI = '/accounts';
    const URI_SUFFIX = '/api_keys';

    public function __construct(int $accountId)
    {
        $this->setAccountId($accountId);
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
        return parent::getUri() . '/' . $this->getAccountId() .  static::URI_SUFFIX;
    }
}
