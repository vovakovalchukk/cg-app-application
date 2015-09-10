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
    const ROUTE_THREAD = 'Thread';

    protected $viewModelFactory;
    protected $userOrganisationUnitService;
    protected $userService;
    protected $service;

    protected $filterNameMap = [
        'eu' => 'externalUsername'
    ];

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
        $threadId = $this->params('threadId');
        $filter = $this->params()->fromQuery('f');
        if ($filter && isset($this->filterNameMap[$filter])) {
            $filter = $this->filterNameMap[$filter];
        }
        $filterValue = $this->params()->fromQuery('fv');
        $user = $this->userOrganisationUnitService->getActiveUser();
        $rootOu = $this->userOrganisationUnitService->getRootOuByUserEntity($user);
        $view->setVariable('uri', static::ROUTE_INDEX_URL);
        $view->setVariable('threadId', $threadId);
        $view->setVariable('filter', $filter);
        $view->setVariable('filterValue', $filterValue);
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('userId', $user->getId());
        $view->setVariable('assignableUsersArray', $this->userService->getUserOptionsArray($rootOu));
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->addChild($this->getFilterSearchInputView(), 'filterSearchInput');
        $view->addChild($this->getFilterSearchButtonView(), 'filterSearchButton');
        return $view;
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