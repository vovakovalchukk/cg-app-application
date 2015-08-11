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

    public function fetchHeadlineDataForActiveUser()
    {
        $activeRootOu = $this->userOuService->getRootOuByActiveUser();
        $headline = $this->headlineService->fetch($activeRootOu->getId());
        return $this->formatHeadlineData($headline);
    }

    protected function formatHeadlineData(Headline $headline)
    {
        $user = $this->userOuService->getActiveUser();
        $headlineData = $headline->toArray();
        $headlineData['myMessages'] = 0;
        if (isset($headlineData['assigned'][$user->getId()])) {
            $headlineData['myMessages'] = number_format($headlineData['assigned'][$user->getId()]);
        }
        $assignedTotal = 0;
        foreach ($headlineData['assigned'] as $userId => $count) {
            $assignedTotal += $count;
        }
        $headlineData['assigned'] = number_format($assignedTotal);
        $headlineData['unassigned'] = number_format($headlineData['unassigned']);
        $headlineData['resolved'] = number_format($headlineData['resolved']);
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
