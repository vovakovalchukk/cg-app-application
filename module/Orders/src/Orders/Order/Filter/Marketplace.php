<?php
namespace Orders\Order\Filter;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Filters\SelectOptionsAsTitleValuesInterface;
use CG\User\ActiveUserInterface;
use Orders\Order\Filter\Marketplace\Channel\Factory as ChannelFactory;

class Marketplace implements SelectOptionsAsTitleValuesInterface
{
    /** @var AccountService */
    protected $accountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var ChannelFactory */
    protected $channelFactory;

    public function __construct(AccountService $accountService, ActiveUserInterface $activeUserContainer, ChannelFactory $channelFactory)
    {
        $this->accountService = $accountService;
        $this->activeUserContainer = $activeUserContainer;
        $this->channelFactory = $channelFactory;
    }

    /**
     * @inheritDoc
     */
    public function getSelectOptionsAsTitleValues(): array
    {
        $options = [];
        $accounts = $this->fetchSalesAccounts();
        foreach ($accounts as $account) {
            $accountOptions = $this->getOptionsForAccount($account);
            $options = array_merge($options, $accountOptions);
        }
        return $options;
    }
    
    protected function fetchSalesAccounts(): AccountCollection
    {
        try {
            $filter = (new AccountFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setRootOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()])
                ->setActive(true)
                ->setDeleted(false)
                ->setType(ChannelType::SALES);
            return $this->accountService->fetchByFilter($filter);
        } catch (NotFound $e) {
            return new AccountCollection(Account::class, 'empty');
        }
    }

    protected function getOptionsForAccount(Account $account): array
    {
        try {
            $channelClass = ($this->channelFactory)($account);
            return $channelClass($account);
        } catch (\InvalidArgumentException $e) {
            // Unsupported channel
            return [];
        }
    }
}