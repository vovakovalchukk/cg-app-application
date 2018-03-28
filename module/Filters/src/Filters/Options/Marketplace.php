<?php
namespace Filters\Options;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Collection as Accounts;
use CG\Listing\Unimported\Marketplace\Client\Service as MarketplaceService;
use CG\Listing\Unimported\Marketplace\Entity;
use CG\Listing\Unimported\Marketplace\Collection;
use CG\Listing\Unimported\Marketplace\Filter as MarketplaceFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Filters\SelectOptionsInterface;

class Marketplace implements SelectOptionsInterface
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var MarketplaceService */
    protected $marketplaceService;
    /** @var AccountService */
    protected $accountService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        MarketplaceService $marketplaceService,
        AccountService $accountService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->marketplaceService = $marketplaceService;
        $this->accountService = $accountService;
    }

    public function getSelectOptions()
    {
        return iterator_to_array($this->getSelectOptionsIterator());
    }

    protected function getSelectOptionsIterator(): \Traversable
    {
        try {
            /** @var Collection $marketplaces */
            $marketplaces = $this->marketplaceService->fetchCollectionByFilter(
                new MarketplaceFilter($this->getActiveUser()->getOuList())
            );
            /** @var Accounts $accounts */
            $accounts = $this->accountService->fetchByFilter(
                (new AccountFilter('all', 1))->setId($marketplaces->getArrayOf('accountId'))
            );
        } catch (NotFound $exception) {
            return;
        }

        /** @var Entity $marketplace */
        foreach ($marketplaces as $marketplace) {
            $account = $accounts->getById($marketplace->getAccountId());
            yield $marketplace->getMarketplace() => $this->marketplaceService->mapMarketplaceIdToName($account, $marketplace->getMarketplace());
        }
    }

    protected function getActiveUser(): User
    {
        return $this->activeUserContainer->getActiveUser();
    }
} 
