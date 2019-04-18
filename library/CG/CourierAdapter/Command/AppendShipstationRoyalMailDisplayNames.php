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
    const BATCH_ACCOUNT_LIMIT = 3;
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
        $filter->setChannel([static::SHIPSTATION_RM_CHANNEL]);
        do {
            try {
                $filter->setPage($page++);
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
            $this->saveAccount($account, $output);
        }
    }

    protected function saveAccount(AccountEntity $account, OutputInterface $output): void
    {
        try {
            $this->accountService->save($account);
            $output->writeln(sprintf("Account %s updated: %s", $account->getId(), $account->getDisplayName()));
        } catch (\Throwable $exception) {
            $output->writeln(sprintf("Failed to save account %s due to an exception with the following message:\n %s", $account->getId(), $exception->getMessage()));
        }
    }

    protected function accountNameAlreadyContainsSuffix(AccountEntity $account): bool
    {
        return (strpos($account->getDisplayName(), static::SHIPSTATION_ACCOUNT_SUFFIX) !== false);
    }
}