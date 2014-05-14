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
        return $view;
    }

    protected function getTemplateSelectView()
    {
        $organisationUnitIds = $this->getUserOrganisationUnitService()->getAncestorOrganisationUnitIdsByActiveUser();
        $templates = $this->getTemplateService()->fetchInvoiceCollectionByOrganisationUnitWithHardCoded($organisationUnitIds);
        $options = [];
        foreach ($templates as $template) {
            $options[] = [
                "title" => $template->getName(),
                "value" => $template->getId(),
            ];
        }
        $templateView = $this->getViewModelFactory()->newInstance(["options" => $options]);
        $templateView->setTemplate('elements/custom-select.mustache');
        $templateView->setVariable('name', 'template');
        $templateView->setVariable('initialTitle', $this->getTranslate()->translate('Select Template'));
        return $templateView;
    }

    protected function getTemplateAddButtonView()
    {
        $templateAddButtonView = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->getTranslate()->translate('New Template'),
            'id' => 'new-template'
        ]);
        $templateAddButtonView->setTemplate('elements/buttons.mustache');
        return $templateAddButtonView;
    }

    protected function getTemplateDuplicateButtonView()
    {
        $templateDuplicateButtonView = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->getTranslate()->translate('Duplicate'),
            'disabled' => true,
            'id' => 'duplicate-template'
        ]);
        $templateDuplicateButtonView->setTemplate('elements/buttons.mustache');
        return $templateDuplicateButtonView;
    }

    protected function getTemplateDiscardButtonView()
    {
        $templateDuplicateButtonView = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->getTranslate()->translate('Discard'),
            'id' => 'discard-template'
        ]);
        $templateDuplicateButtonView->setTemplate('elements/buttons.mustache');
        return $templateDuplicateButtonView;
    }

    protected function getTemplateSaveButtonView()
    {
        $templateDuplicateButtonView = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->getTranslate()->translate('Save'),
            'id' => 'save-template'
        ]);
        $templateDuplicateButtonView->setTemplate('elements/buttons.mustache');
        return $templateDuplicateButtonView;
    }

    public function fetchAction()
    {
        $template = $this->getTemplateService()->fetchAsJson($this->params()->fromPost('id'));
        $view = $this->getJsonModelFactory()->newInstance(["template" => $template]);
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
}
