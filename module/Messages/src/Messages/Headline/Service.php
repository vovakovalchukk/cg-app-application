<?php
namespace Messages\Headline;

use CG\Communication\Headline\Entity as Headline;
use CG\Communication\Headline\Service as HeadlineService;
use CG\OrganisationUnit\Service as OuService;
use CG\Permission\Exception as PermissionException;
use CG\User\OrganisationUnit\Service as UserOuService;

class Service
{
    protected $headlineService;
    protected $ouService;
    protected $userOuService;

    public function __construct(
        HeadlineService $headlineService,
        OuService $ouService,
        UserOuService $userOuService
    ) {
        $this->setHeadlineService($headlineService)
            ->setOuService($ouService)
            ->setUserOuService($userOuService);
    }

    public function fetchHeadlineDataForOuId($organisationUnitId)
    {
        // Security check
        $requestedRootOu = $this->ouService->getRootOuFromOuId($organisationUnitId);
        $activeRootOu = $this->userOuService->getRootOuByActiveUser();
        if ($requestedRootOu->getId() != $activeRootOu->getId()) {
            throw new PermissionException();
        }
        $headline = $this->headlineService->fetch($organisationUnitId);
        return $this->formatHeadlineData($headline);
    }

    protected function formatHeadlineData(Headline $headline)
    {
        $user = $this->userOuService->getActiveUser();
        $headlineData = $headline->toArray();
        $headlineData['myMessages'] = 0;
        if (isset($headlineData['assigned'][$user->getId()])) {
            $headlineData['myMessages'] = $headlineData['assigned'][$user->getId()];
        }
        $assignedTotal = 0;
        foreach ($headlineData['assigned'] as $userId => $count) {
            $assignedTotal += $count;
        }
        $headlineData['assigned'] = $assignedTotal;
        return $headlineData;
    }

    protected function setHeadlineService(HeadlineService $headlineService)
    {
        $this->headlineService = $headlineService;
        return $this;
    }

    protected function setOuService(OuService $ouService)
    {
        $this->ouService = $ouService;
        return $this;
    }

    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }
}
