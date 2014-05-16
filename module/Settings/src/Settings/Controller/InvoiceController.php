<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Zend\Stdlib\Mvc\Model\Helper\Translate;

class InvoiceController extends AbstractActionController
{
    const ROUTE = 'Invoice';
    const ROUTE_FETCH = 'Fetch';
    const ROUTE_SAVE = 'Save';
    const TEMPLATE_SELECTOR_ID = 'template-selector';

    protected $viewModelFactory;
    protected $jsonModelFactory;
    protected $templateService;
    protected $userOrganisationUnitService;
    protected $translate;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        Translate $translate
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setTemplateService($templateService)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setTranslate($translate);
    }

    public function designAction()
    {
        $view = $this->getViewModelFactory()->newInstance();        
        $view->addChild($this->getTemplateSelectView(), 'templates');
        $view->addChild($this->getTemplateAddButtonView(), 'templateAddButton');
        $view->addChild($this->getTemplateDuplicateButtonView(), 'templateDuplicateButton');
        $view->addChild($this->getTemplateDiscardButtonView(), 'templateDiscardButton');
        $view->addChild($this->getTemplateSaveButtonView(), 'templateSaveButton');
        $view->setVariable('templateSelectorId', static::TEMPLATE_SELECTOR_ID);

        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');
        
        return $view;
    }

    public function saveAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $this->getTemplateService()->saveFromJson($this->params()->fromPost('template'));
        return $view;
    }

    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    public function setTemplateService(TemplateService $templateService)
    {
        $this->templateService = $templateService;
        return $this;
    }

    public function getTemplateService()
    {
        return $this->templateService;
    }

    public function setUserOrganisationUnitService(UserOrganisationUnitService $userOrganisationUnitService)
    {
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        return $this;
    }

    public function getUserOrganisationUnitService()
    {
        return $this->userOrganisationUnitService;
    }

    public function setTranslate(Translate $translate)
    {
        $this->translate = $translate;
        return $this;
    }

    public function getTranslate()
    {
        return $this->translate;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function getPaperTypeModule()
    {
        $dropDownConfig = [
            "isOptional" => false,
            "id" => "paperTypeDropdown",
            "name" => "paperTypeDropdown",
            "class" => "",
            "options" => []
        ];

        $paperTypeModule = $this->getViewModelFactory()->newInstance($dropDownConfig);
        $paperTypeModule->setTemplate('InvoiceDesigner/Template/paperType');

        return $paperTypeModule;
    }
}
