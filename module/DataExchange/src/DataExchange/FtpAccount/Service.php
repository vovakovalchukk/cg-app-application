<?php
namespace DataExchange\FtpAccount;

use CG\FtpAccount\Collection as FtpAccountCollection;
use CG\FtpAccount\Entity as FtpAccount;
use CG\FtpAccount\Filter as FtpAccountFilter;
use CG\FtpAccount\Mapper as FtpAccountMapper;
use CG\FtpAccount\PasswordCryptor as PasswordCryptor;
use CG\FtpAccount\Service as FtpAccountService;
use CG\FtpAccount\Tester as FtpAccountTester;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var FtpAccountService */
    protected $ftpAccountService;
    /** @var FtpAccountMapper */
    protected $ftpAccountMapper;
    /** @var PasswordCryptor */
    protected $passwordCryptor;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var FtpAccountTester */
    protected $ftpAccountTester;

    public function __construct(
        FtpAccountService $ftpAccountService,
        FtpAccountMapper $ftpAccountMapper,
        PasswordCryptor $passwordCryptor,
        ActiveUserInterface $activeUserContainer,
        FtpAccountTester $ftpAccountTester
    ) {
        $this->ftpAccountService = $ftpAccountService;
        $this->ftpAccountMapper = $ftpAccountMapper;
        $this->passwordCryptor = $passwordCryptor;
        $this->activeUserContainer = $activeUserContainer;
        $this->ftpAccountTester = $ftpAccountTester;
    }

    public function fetchAllForActiveUser(): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildFilterForRootOu($rootOuId);
            $ftpAccounts = $this->ftpAccountService->fetchCollectionByFilter($filter);
            $this->decryptPasswords($ftpAccounts);
            return $ftpAccounts->toArray();
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function buildFilterForRootOu(int $rootOuId): FtpAccountFilter
    {
        return (new FtpAccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId]);
    }

    protected function decryptPasswords(FtpAccountCollection $ftpAccounts): FtpAccountCollection
    {
        /** @var FtpAccount $ftpAccount */
        foreach ($ftpAccounts as $ftpAccount) {
            if ($ftpAccount->getPassword() === null) {
                continue;
            }
            $ftpAccount->setPassword($this->passwordCryptor->decrypt($ftpAccount->getPassword()));
        }
        return $ftpAccounts;
    }

    public function getTypeOptions(): array
    {
        return array_map(function (string $type) {
            return strtoupper($type);
        }, FtpAccount::getAllTypes());
    }

    public function getDefaultPorts(): array
    {
        return FtpAccount::getAllDefaultPorts();
    }

    public function saveForActiveUser(array $data): FtpAccount
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $data['organisationUnitId'] = $rootOuId;
        if (isset($data['password'])) {
            $data['password'] = $this->passwordCryptor->encrypt($data['password']);
        }
        if (!isset($data['id'])) {
            return $this->saveNew($data);
        }
        return $this->saveExisting($data);
    }

    protected function saveNew(array $data): FtpAccount
    {
        $entity = $this->ftpAccountMapper->fromArray($data);
        return $this->save($entity);
    }

    protected function saveExisting(array $data): FtpAccount
    {
        $fetchedEntity = $this->ftpAccountService->fetch($data['id']);
        $entityArray = array_merge($fetchedEntity->toArray(), $data);
        $updatedEntity = $this->ftpAccountMapper->fromArray($entityArray);
        $updatedEntity->setStoredETag($data['etag'] ?? $fetchedEntity->getStoredETag());
        return $this->save($updatedEntity);
    }

    protected function save(FtpAccount $entity): FtpAccount
    {
        $entityHal = $this->ftpAccountService->save($entity);
        $entity = $this->ftpAccountMapper->fromHal($entityHal);
        if (!$entity->getStoredETag()) {
            return $this->ftpAccountService->fetch($entity->getId());
        }
        return $entity;
    }

    public function remove(int $id): void
    {
        $entity = $this->ftpAccountService->fetch($id);
        $this->ftpAccountService->remove($entity);
    }

    public function testConnection(int $id): bool
    {
        $entity = $this->ftpAccountService->fetch($id);
        return ($this->ftpAccountTester)($entity);
    }
}