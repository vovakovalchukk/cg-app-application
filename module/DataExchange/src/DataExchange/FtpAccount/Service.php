<?php
namespace DataExchange\FtpAccount;

use CG\FtpAccount\Entity as FtpAccount;
use CG\FtpAccount\Filter as FtpAccountFilter;
use CG\FtpAccount\Service as FtpAccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var FtpAccountService */
    protected $ftpAccountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        FtpAccountService $ftpAccountService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->ftpAccountService = $ftpAccountService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function fetchAllForActiveUser(): array
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildFilterForRootOu($rootOuId);
            $ftpAccounts = $this->ftpAccountService->fetchCollectionByFilter($filter);
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
}