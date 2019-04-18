<?php
namespace CG\CourierAdapter\Command;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Output\OutputInterface;

class AppendShipstationRoyalMailDisplayNames
{
    const BATCH_ACCOUNT_LIMIT = 300;
    const SHIPSTATION_RM_CHANNEL = 'royal-mail-ss';
    const SHIPSTATION_ACCOUNT_SUFFIX = ' - Shipstation';

    /** @var AccountService */
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function __invoke(OutputInterface $output): void
    {
        foreach ($this->getBatchOfAccounts() as $accountCollection) {
            $this->renameAccounts($accountCollection, $output);
        }
    }

    protected function getBatchOfAccounts(): \Generator
    {
        $page = 1;
        $filter = new AccountFilter(static::BATCH_ACCOUNT_LIMIT, $page);
        $filter->setChannel(static::SHIPSTATION_RM_CHANNEL);
        do {
            try {
                yield $this->accountService->fetchByFilter($filter);
            } catch (NotFound $exception) {
                $page = false;
            }
        } while ($page);
    }

    protected function renameAccounts(AccountCollection $accountCollection, OutputInterface $output): void
    {
        /** @var AccountEntity $account */
        foreach ($accountCollection as $account) {
            if ($this->accountNameAlreadyContainsSuffix($account)) {
                continue;
            }
            $displayName = $account->getDisplayName() . static::SHIPSTATION_ACCOUNT_SUFFIX;
            $account->setDisplayName($displayName);
        }
        $this->saveAccountCollection($accountCollection, $output);
    }

    protected function saveAccountCollection(AccountCollection $accountCollection, OutputInterface $output): void
    {
        try {
            $this->accountService->saveCollection($accountCollection);
        } catch (\Throwable $exception) {
            $output->writeln("Failed to save a batch of accounts due to an exception with the following message:\n{$exception->getMessage()}");
        }
    }

    protected function accountNameAlreadyContainsSuffix(AccountEntity $account): bool
    {
        return (strpos($account->getDisplayName(), static::SHIPSTATION_ACCOUNT_SUFFIX) !== false);
    }
}