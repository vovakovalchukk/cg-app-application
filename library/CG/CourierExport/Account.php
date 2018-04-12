<?php
namespace CG\CourierExport;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url;
use CourierExport\Controller\AccountController;

abstract class Account implements AccountInterface
{
    /** @var Url */
    protected $url;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        return $this->url->fromRoute($this->getRoute(), $this->getParams($account), $this->getOptions());
    }

    protected function getRoute(): string
    {
        return AccountController::ROUTE_SETUP;
    }

    protected function getParams(AccountEntity $account): array
    {
        return [
            'channel' => $account->getChannel(),
            'account' => $account->getId(),
        ];
    }

    protected function getOptions(): array
    {
        return [];
    }
}