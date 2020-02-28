<?php
namespace Messages\Controller;

use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\User\Service as UserService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Messages\Message\Template\Service as MessageTemplateService;
use Messages\Thread\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public const ROUTE_INDEX_URL = '/messages';
    public const ROUTE_THREAD = 'Thread';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var UserOrganisationUnitService */
    protected $userOrganisationUnitService;
    /** @var UserService */
    protected $userService;
    /** @var Service */
    protected $service;
    /** @var MessageTemplateService */
    protected $messageTemplateService;

    protected $filterNameMap = [
        'eu' => 'externalUsername'
    ];

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UserOrganisationUnitService $userOrganisationUnitService,
        UserService $userService,
        Service $service,
        MessageTemplateService $messageTemplateService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        $this->userService = $userService;
        $this->service = $service;
        $this->messageTemplateService = $messageTemplateService;
    }

    public function indexAction(): ViewModel
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
        $view->setVariable('messageTemplates', $this->messageTemplateService->fetchAllForActiveOuAsArray());
        $view->setVariable('messageTemplateTags', $this->messageTemplateService->getTemplateTagOptions());
        $view->setVariable('accounts', $this->messageTemplateService->fetchAllSalesAccountsForActiveOuAsOptions());
        $view->addChild($this->getFilterSearchInputView(), 'filterSearchInput');
        $view->addChild($this->getFilterSearchButtonView(), 'filterSearchButton');
        return $view;
    }

    protected function getFilterSearchInputView(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/search.mustache');
        $view->setVariable('name', 'searchTerm');
        $view->setVariable('placeholder', 'Search for...');
        return $view;
    }

    protected function getFilterSearchButtonView(): ViewModel
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
}