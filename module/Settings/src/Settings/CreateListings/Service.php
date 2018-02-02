<?php
namespace Settings\CreateListings;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var AccountService */
    protected $accountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(AccountService $accountService, ActiveUserInterface $activeUserContainer)
    {
        $this->accountService = $accountService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function getSalesAccountsAsSelectOptions(): array
    {
        $accounts = $this->getSalesAccounts();
        return $this->convertAccountCollectionToSelectOptions($accounts);
    }

    protected function getSalesAccounts(): AccountCollection
    {
        try {
            return $this->accountService->fetchByFilter($this->buildSalesAccountsFilter());
        } catch (NotFound $e) {
            return new AccountCollection(Account::class, 'empty');
        }
    }

    protected function buildSalesAccountsFilter(): AccountFilter
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType(ChannelType::SALES)
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
        // TEMPORARY. We should remove this once all channels support importing
        $filter->setChannel(['ebay', 'shopify', 'big-commerce']);
        return $filter;
    }

    protected function convertAccountCollectionToSelectOptions(AccountCollection $accounts): array
    {
        $options = [];
        if ($accounts->count() == 0) {
            $options[] = [
                'title' => 'No suitable accounts',
                'value' => '-1'
            ];
            return $options;
        }
        /** @var Account $account */
        foreach ($accounts as $account) {
            $options[] = [
                'title' => $account->getDisplayName(),
                'value' => $account->getId()
            ];
        }
        return $options;
    }
}