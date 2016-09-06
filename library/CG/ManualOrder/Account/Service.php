<?php
namespace CG\ManualOrder\Account;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Shared\Filter as AccountFilter;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const MAX_ID_ATTEMPTS = 3;
    const LOG_CODE = 'ManualOrderAccountService';
    const LOG_FETCHED = 'Fetched existing manual order Account (%d) for OU %d';
    const LOG_CREATED = 'Created new manual order Account (%d) for OU %d';
    const LOG_ORDER_ID = 'Current manual order ID for Account %d (OU %d) is %d, will increment to %d';
    const LOG_ORDER_ID_CONFLICT = 'Conflict when incrementing manual order ID for Account %d (OU %d), will try again';

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
        $account = $accounts->current();
        $this->logDebug(static::LOG_FETCHED, ['account' => $account->getId(), 'ou' => $organisationUnit->getId()], [static::LOG_CODE, 'Fetched']);
        return $account;
    }

    protected function createAccountForOrganisationUnit(OrganisationUnit $organisationUnit)
    {
        $account = $this->creationService->connectAccount($organisationUnit->getId());
        $this->logDebug(static::LOG_CREATED, ['account' => $account->getId(), 'ou' => $organisationUnit->getId()], [static::LOG_CODE, 'Created']);
        return $account;
    }

    public function getNextOrderIdForAccount(AccountEntity $account, $attempt = 1)
    {
        $externalData = $account->getExternalData();
        $currentOrderId = (isset($externalData['currentOrderId']) ? $externalData['currentOrderId'] : 0);
        $newOrderId = $currentOrderId + 1;
        $externalData['currentOrderId'] = $newOrderId;
        $account->setExternalData($externalData);
        $this->logDebug(static::LOG_ORDER_ID, ['account' => $account->getId(), 'ou' => $account->getOrganisationUnitId(), $currentOrderId, $newOrderId], [static::LOG_CODE, 'OrderId']);

        try {
            // Make sure we can save the new ID before returning it
            $this->accountService->save($account);
            return $newOrderId;

        } catch (Conflict $e) {
            if ($attempt >= static::MAX_ID_ATTEMPTS) {
                throw $e;
            }
            // Someone else may have used our ID, try again.
            $this->logDebug(static::LOG_ORDER_ID_CONFLICT, ['account' => $account->getId(), 'ou' => $account->getOrganisationUnitId()], [static::LOG_CODE, 'Conflict']);
            $fetchedAccount = $this->accountService->fetch($account->getId());
            return $this->getNextOrderIdForAccount($fetchedAccount, ++$attempt);
        }
        // Allow NotModifieds to be thrown, the entity definitely should be modified
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