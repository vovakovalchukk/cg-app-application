<?php
namespace CG\ManualOrder\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    /** @var AccountService */
    protected $accountService;
    /** @var CreationService */
    protected $creationService;

    public function __construct(AccountService $accountService, CreationService $creationService)
    {
        $this->setAccountService($accountService)
            ->setCreationService($creationService);
    }

    /**
     * @return \CG\Account\Shared\Entity
     */
    public function getAccountForOrganisationUnit(OrganisationUnit $organisationUnit)
    {
        try {
            return $this->fetchAccountForOrganisationUnit($organisationUnit);
        } catch (NotFound $e) {
            return $this->createAccountForOrganisationUnit($organisationUnit);
        }
    }

    protected function fetchAccountForOrganisationUnit(OrganisationUnit $organisationUnit)
    {
        $filter = (new AccountFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setChannel([CreationService::CHANNEL])
            ->setOrganisationUnitId([$organisationUnit->getId()]);
        $includeInvisible = true;
        $accounts = $this->accountService->fetchByFilter($filter, $includeInvisible);
        $accounts->rewind();
        return $accounts->current();
    }

    protected function createAccountForOrganisationUnit(OrganisationUnit $organisationUnit)
    {
        return $this->creationService->connectAccount($organisationUnit->getId());
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setCreationService(CreationService $creationService)
    {
        $this->creationService = $creationService;
        return $this;
    }
}