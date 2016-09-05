<?php
namespace CG\ManualOrder\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    const MAX_ID_ATTEMPTS = 3;

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
     * @param OrganisationUnit $organisationUnit The Trading Company (or root OU) to get an Account for, as selected by the user
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

    public function getNextOrderIdForAccount(AccountEntity $account, $attempt = 1)
    {
        $externalData = $account->getExternalData();
        $currentOrderId = (isset($externalData['currentOrderId']) ? $externalData['currentOrderId'] : 0);
        $newOrderId = $currentOrderId++;
        $externalData['currentOrderId'] = $newOrderId;
        $account->setExternalData($externalData);

        try {
            // Make sure we can save the new ID before returning it
            $this->accountService->save($account);
            return $account->getId() . '-' . $newOrderId;

        } catch (Conflict $e) {
            if ($attempt >= static::MAX_ID_ATTEMPTS) {
                throw $e;
            }
            // Someone else may have used our ID, try again.
            $fetchedAccount = $this->accountService->fetch($account->getId());
            return $this->getNextOrderIdForAccount($fetchedAccount, ++$attempt);
        }
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