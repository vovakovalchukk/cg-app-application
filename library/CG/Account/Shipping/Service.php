<?php
namespace CG\Account\Shipping;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as OUService;

class Service
{
    /** @var AccountService */
    protected $accountService;
    /** @var OUService */
    protected $ouService;
    /** @var Accounts Accounts */
    protected $genericAccounts;

    public function __construct(AccountService $accountService, OUService $ouService)
    {
        $this->accountService = $accountService;
        $this->ouService = $ouService;
        $this->genericAccounts = new Accounts(Account::class, 'GenericAccounts');
    }

    public function registerGenericAccount(GenericAccountProviderInterface $genericAccountProvider)
    {
        $genericAccount = $genericAccountProvider();
        if ($genericAccount) {
            $this->genericAccounts->attach($genericAccount);
        }
    }

    public function fetchShippingAccount(int $accountId): Account
    {
        $account = $this->genericAccounts->getById($accountId) ?? $this->accountService->fetch($accountId);
        if (!$account->hasType(Type::SHIPPING)) {
            throw new NotFound(
                sprintf(
                    'Account %d is not a %s account - Types: %s',
                    $account->getId(),
                    Type::SHIPPING,
                    implode(', ', $account->getTypes())
                )
            );
        }
        return $account;
    }

    public function fetchShippingAccounts(array $accountIds = []): Accounts
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($accountIds)
            ->setRootOrganisationUnitId([$this->ouService->getActiveUser()->getRootOuId()])
            ->setType(Type::SHIPPING)
            ->setDeleted(false)
            ->setActive(true);

        $genericAccounts = $this->getFilteredGenericAccounts($accountIds);
        if ($genericAccounts->count() == 0) {
            return $this->accountService->fetchByFilter($filter);
        }

        try {
            $accounts = $this->accountService->fetchByFilter($filter);
        } catch (NotFound $exception) {
            $accounts = new Accounts(Account::class, 'fetchByFilter', $filter->toArray());
        }

        $accounts->attachAll($genericAccounts);
        return $accounts;
    }

    protected function getFilteredGenericAccounts(array $accountIds = []): Accounts
    {
        if (empty($accountIds)) {
            return $this->genericAccounts;
        }

        $accounts = array_fill_keys($accountIds, true);
        $genericAccounts = new Accounts(Account::class, 'GenericAccounts', compact('accountIds'));

        /** @var Account $genericAccount */
        foreach ($this->genericAccounts as $genericAccount) {
            if (isset($accounts[$genericAccount->getId()])) {
                $genericAccounts->attach($genericAccount);
            }
        }

        return $genericAccounts;
    }
}