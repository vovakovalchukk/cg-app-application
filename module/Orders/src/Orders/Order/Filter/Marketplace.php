<?php
namespace Orders\Order\Filter;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Filters\SelectOptions\TitleValue;
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
        $accounts = $this->fetchSalesAccounts();
        $optionsByChannel = $this->getUniqueOptionsByChannel($accounts);
        return $this->combineOptionsByChannel($optionsByChannel);
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

    /**
     * @return array [channel => TitleValue[]]
     */
    protected function getUniqueOptionsByChannel(AccountCollection $accounts): array
    {
        $optionsByChannel = [];
        foreach ($accounts as $account) {
            $accountOptions = $this->getOptionsForAccount($account);
            if (!isset($optionsByChannel[$account->getChannel()])) {
                $optionsByChannel[$account->getChannel()] = [];
            }
            // This gives us unique options per channel as they are keyed on value
            // We DONT want them to be unique across all channels as e.g. both Amazon and eBay have a "UK" marketplace
            $optionsByChannel[$account->getChannel()] = array_merge($optionsByChannel[$account->getChannel()], $accountOptions);
        }
        return $optionsByChannel;
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

    /**
     * @param $optionsByChannel [channel => TitleValue[]]
     * @return TitleValue[]
     */
    protected function combineOptionsByChannel(array $optionsByChannel): array
    {
        $allOptions = [];
        foreach ($optionsByChannel as $channel => $options) {
            // The options themselves are keyed but we don't want those keys now
            $allOptions = array_merge($allOptions, array_values($options));
        }
        return $allOptions;
    }
}