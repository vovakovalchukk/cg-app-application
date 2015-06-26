<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use Messages\Module;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    const ROUTE_INDEX_URL = '/messages';

    protected $viewModelFactory;
    protected $userOrganisationUnitService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOrganisationUnitService $userOrganisationUnitService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setUserOrganisationUnitService($userOrganisationUnitService);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $user = $this->userOrganisationUnitService->getActiveUser();
        $rootOu = $this->userOrganisationUnitService->getRootOuByUserEntity($user);
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('userId', $user->getId());
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
}