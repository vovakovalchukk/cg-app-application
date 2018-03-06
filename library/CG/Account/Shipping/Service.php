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

    public function __construct(AccountService $accountService, OUService $ouService)
    {
        $this->accountService = $accountService;
        $this->ouService = $ouService;
    }

    public function fetchShippingAccount(int $accountId): Account
    {
        $account = $this->accountService->fetch($accountId);
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
        return $this->accountService->fetchByFilter(
            (new AccountFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setId($accountIds)
                ->setOrganisationUnitId($this->ouService->getAncestorOrganisationUnitIdsByActiveUser())
                ->setType(Type::SHIPPING)
                ->setDeleted(false)
                ->setActive(true)
        );
    }
}