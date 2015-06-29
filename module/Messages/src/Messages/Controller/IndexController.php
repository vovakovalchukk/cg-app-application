<?php
namespace Messages\Controller;

use CG\Communication\Thread\Status as ThreadStatus;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use Messages\Module;
use Messages\Thread\Service;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    const ROUTE_INDEX_URL = '/messages';

    protected $viewModelFactory;
    protected $userOrganisationUnitService;
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOrganisationUnitService $userOrganisationUnitService,
        Service $service
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setService($service);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $user = $this->userOrganisationUnitService->getActiveUser();
        $rootOu = $this->userOrganisationUnitService->getRootOuByUserEntity($user);
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('userId', $user->getId());
        $view->setVariable('myMessagesCount', $this->service->getAssigneeCount(Service::ASSIGNEE_ACTIVE_USER));
        $view->setVariable('unassignedCount', $this->service->getAssigneeCount(Service::ASSIGNEE_UNASSIGNED));
        $view->setVariable('assignedCount', $this->service->getAssigneeCount(Service::ASSIGNEE_ASSIGNED));
        $view->setVariable('resolvedCount', $this->service->getStatusCount(ThreadStatus::RESOLVED));
        $view->setVariable('isSidebarVisible', false);
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setUserOrganisationUnitService(UserOrganisationUnitService $userOrganisationUnitService)
    {
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}