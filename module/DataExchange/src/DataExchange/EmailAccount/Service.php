<?php
namespace DataExchange\EmailAccount;

use CG\EmailAccount\Entity as EmailAccount;
use CG\EmailAccount\Mapper as EmailAccountMapper;
use CG\EmailAccount\Filter as EmailAccountFilter;
use CG\EmailAccount\Service as EmailAccountService;
use CG\EmailVerification\Service as EmailVerificationService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var EmailAccountService */
    protected $emailAccountService;
    /** @var EmailAccountMapper */
    protected $emailAccountMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var EmailVerificationService */
    protected $emailVerificationService;

    public function __construct(
        EmailAccountService $emailAccountService,
        EmailAccountMapper $emailAccountMapper,
        ActiveUserInterface $activeUserContainer,
        EmailVerificationService $emailVerificationService
    ) {
        $this->emailAccountService = $emailAccountService;
        $this->emailAccountMapper = $emailAccountMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->emailVerificationService = $emailVerificationService;
    }

    public function fetchAllForActiveUser(): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildFilterForRootOu($rootOuId);
            $emailAccounts = $this->emailAccountService->fetchCollectionByFilter($filter);
            return $emailAccounts->toArray();
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildFilterForRootOu(int $rootOuId): EmailAccountFilter
    {
        return (new EmailAccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId]);
    }

    public function saveForActiveUser(array $data): EmailAccount
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $data['organisationUnitId'] = $rootOuId;
        if (isset($data['verified'])) {
            $data['verified'] = filter_var($data['verified'], FILTER_VALIDATE_BOOLEAN);
        }
        if (!isset($data['id'])) {
            return $this->saveNew($data);
        }
        return $this->saveExisting($data);
    }

    protected function saveNew(array $data): EmailAccount
    {
        $entity = $this->emailAccountMapper->fromArray($data);
        return $this->save($entity);
    }

    protected function saveExisting(array $data): EmailAccount
    {
        $fetchedEntity = $this->emailAccountService->fetch($data['id']);
        $entityArray = array_merge($fetchedEntity->toArray(), $data);
        $updatedEntity = $this->emailAccountMapper->fromArray($entityArray);
        $updatedEntity->setStoredETag($data['etag'] ?? $fetchedEntity->getStoredETag());
        return $this->save($updatedEntity);
    }

    protected function save(EmailAccount $entity): EmailAccount
    {
        $entityHal = $this->emailAccountService->save($entity);
        $entity = $this->emailAccountMapper->fromHal($entityHal);
        if (!$entity->getStoredETag()) {
            return $this->emailAccountService->fetch($entity->getId());
        }
        return $entity;
    }

    public function remove(int $id): void
    {
        $entity = $this->emailAccountService->fetch($id);
        $this->emailAccountService->remove($entity);
    }

    public function verify(int $id): string
    {
        /** @var EmailAccount $entity */
        $entity = $this->emailAccountService->fetch($id);
        $this->emailVerificationService->verifyEmailIdentity($entity->getAddress());
        $status = $this->emailVerificationService->getVerificationStatus($entity->getAddress());
        $isVerified = $this->emailVerificationService->isStatusVerified($status);

        try {
            $entity->setVerificationStatus($status)
                ->setVerified($isVerified);
            $this->emailAccountService->save($entity);
        } catch (NotModified $e) {
            // No-op
        }
        return $status;
    }
}