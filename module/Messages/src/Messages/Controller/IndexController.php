<?php
namespace Messages\Controller;

use CG\Communication\Thread\Status as ThreadStatus;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\User\Service as UserService;
use Messages\Module;
use Messages\Thread\Service;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    const ROUTE_INDEX_URL = '/messages';

    protected $viewModelFactory;
    protected $userOrganisationUnitService;
    protected $userService;
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOrganisationUnitService $userOrganisationUnitService,
        UserService $userService,
        Service $service
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setUserService($userService)
            ->setService($service);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $user = $this->userOrganisationUnitService->getActiveUser();
        $rootOu = $this->userOrganisationUnitService->getRootOuByUserEntity($user);
        $headlineData = $this->service->fetchHeadlineData();
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('userId', $user->getId());
        $view->setVariable('myMessagesCount', $headlineData['assigned'][$user->getId()]);
        $view->setVariable('unassignedCount', $headlineData['unassigned']);
        $view->setVariable('assignedCount', $headlineData['assignedTotal']);
        $view->setVariable('resolvedCount', $headlineData['resolved']);
        $view->setVariable('assignableUsersArray', $this->getAssignableUsersArray($rootOu));
        $view->setVariable('isSidebarVisible', false);
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->addChild($this->getFilterSearchInputView(), 'filterSearchInput');
        $view->addChild($this->getFilterSearchButtonView(), 'filterSearchButton');
        return $view;
    }

    protected function getAssignableUsersArray($rootOu)
    {
        $users = $this->userService->fetchCollection('all', 1, $rootOu->getId());
        $userArray = [];
        foreach ($users as $user) {
            $userArray[$user->getId()] = $user->getFirstName() . ' ' . $user->getLastName();
        }
        return $userArray;
    }

    protected function getFilterSearchInputView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/search.mustache');
        $view->setVariable('name', 'searchTerm');
        $view->setVariable('placeholder', 'Search for...');
        return $view;
    }

    protected function getFilterSearchButtonView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/buttons.mustache');
        $view->setVariable('buttons', [
            [
                'id' => 'filter-search-button',
                'action' => 'search',
                'value' => 'Search',
                'type' => 'button',
            ]
        ]);
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

    protected function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}