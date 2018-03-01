<?php

namespace Settings\Category\Template;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Account\Shared\Entity as Account;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Listing\Channel\Service as ChannelService;

class Service
{
    /** @var  AccountService */
    protected $accountService;
    /** @var  ChannelService */
    protected $channelService;

    const SALES_PLATFORMS = [
        'ebay' => 'ebay',
        'amazon' => 'amazon'
    ];

    public function __construct(AccountService $accountService, ChannelService $channelService)
    {
        $this->accountService = $accountService;
        $this->channelService = $channelService;
    }

    public function fetchAccounts(OrganisationUnit $ou): array
    {
        try {
            $accounts = $this->fetchActiveAccountsForOu($ou);
        } catch (NotFound $e) {
            return [];
        }

        $allowedChannels = $this->channelService->getAllowedCreateListingsChannels($ou, $accounts);
        $result = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!isset($allowedChannels[$account->getChannel()])) {
                continue;
            }
            $displayName = $account->getDisplayName();
            $refreshable = true;
            if (isset(static::SALES_PLATFORMS[$account->getChannel()])) {
                $displayName = $allowedChannels[$account->getChannel()];
                $refreshable = false;
            }
            $result[$account->getId()] = [
                'channel' => $account->getChannel(),
                'displayName' => $displayName,
                'refreshable' => $refreshable
            ];
        }
        return $result;
    }

    protected function fetchActiveAccountsForOu(OrganisationUnit $ou)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$ou->getId()])
            ->setActive(true)
            ->setDeleted(false);
        return $this->accountService->fetchByFilter($filter);
    }
}
