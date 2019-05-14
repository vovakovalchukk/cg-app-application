<?php
namespace Settings\Controller;

use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Category\Template\Service as CategoryTemplateService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ListingTemplatesController extends AbstractActionController
{
    const ROUTE_INDEX = 'Templates';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CategoryTemplateService */
    protected $categoryTemplateService;
    /** @var UserOUService */
    protected $userOuService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        CategoryTemplateService $categoryTemplateService,
        UserOUService $userOuService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->categoryTemplateService = $categoryTemplateService;
        $this->userOuService = $userOuService;
    }

    public function indexAction()
    {
        $view = $this->newViewModel();
        $view->setTemplate('settings/listing/index');

//        $view->setVariable('title', $this->getRouteName())
//            ->setVariable('createRoute', Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS.'/'.static::ROUTE_CREATE, ['type' => $this->params('type')])
//            ->setVariable('type', $this->params('type'))
//            ->addChild($this->getAccountList(), 'accountList')
//            ->addChild($this->getAddChannelSelect(), 'addChannelSelect');
        $view->setVariable('isHeaderBarVisible', true);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    /**
     * @param $variables
     * @param $options
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }
}
