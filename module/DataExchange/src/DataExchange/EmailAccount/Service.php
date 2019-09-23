<?php
namespace DataExchange\EmailAccount;

use CG\EmailAccount\Entity as EmailAccount;
use CG\EmailAccount\Filter as EmailAccountFilter;
use CG\EmailAccount\Service as EmailAccountService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var EmailAccountService */
    protected $emailAccountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(EmailAccountService $emailAccountService, ActiveUserInterface $activeUserContainer)
    {
        $this->emailAccountService = $emailAccountService;
        $this->activeUserContainer = $activeUserContainer;
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
}