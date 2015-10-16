<?php
namespace Settings\Stock;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    /** @var AccountService */
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
    }

    public function getAccountListData(OrganisationUnit $rootOu)
    {
        $accounts = $this->getSalesAccountsForOU($rootOu);
        return $this->formatAccountsAsListData($accounts);
    }

    protected function getSalesAccountsForOU(OrganisationUnit $rootOu)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOu->getId()])
            ->setType(ChannelType::SALES)
            ->setDeleted(false);
        try {
            return $this->accountService->fetchByFilter($filter);
        } catch (NotFound $e) {
            return new AccountCollection(Account::class, __FUNCTION__);
        }
    }

    protected function formatAccountsAsListData(AccountCollection $accounts)
    {
        $data = [];
        foreach ($accounts as $account) {
            $data[] = [
                'id' => $account->getId(),
                'channel' => $account->getChannel(),
                'channelImgUrl' => $account->getImageUrl(),
                'displayName' => $account->getDisplayName(),
                'stockMaximumEnabled' => $account->getStockMaximumEnabled(),
                'stockFixedEnabled' => $account->getStockFixedEnabled(),
            ];
        }
        return $data;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }
}
