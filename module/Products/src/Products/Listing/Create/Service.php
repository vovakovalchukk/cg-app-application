<?php
namespace Products\Listing\Create;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use Products\Listing\Create\ChannelService\Factory;

class Service
{
    /** @var  AccountService */
    protected $accountService;
    /** @var  Factory */
    protected $factory;

    public function __construct(
        AccountService $accountService,
        Factory $factory
    ) {
        $this->accountService = $accountService;
        $this->factory = $factory;
    }

    public function fetchDefaultSettingsForAccount(int $accountId): array
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($accountId);
        return $this->factory->buildChannelService($account->getChannel())->getDefaultSettingsForAccount($account);
    }
}
