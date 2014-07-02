<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Module;
use Settings\Invoice\Service as InvoiceService;
use CG\Template\ReplaceManager\OrderContent as OrderTagManager;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;

class InvoiceController extends AbstractActionController
{
    const ROUTE = 'Invoice';
    const ROUTE_MAPPING = 'Invoice Mapping';
    const ROUTE_DESIGNER = 'Invoice Designer';
    const ROUTE_FETCH = 'Fetch';
    const ROUTE_SAVE = 'Save';
    const TEMPLATE_SELECTOR_ID = 'template-selector';
    const PAPER_TYPE_DROPDOWN_ID = "paper-type-dropdown";

    protected $viewModelFactory;
    protected $jsonModelFactory;
    protected $templateService;
    protected $userOrganisationUnitService;
    protected $orderTagManager;
    protected $invoiceService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        OrderTagManager $orderTagManager,
        InvoiceService $invoiceService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setTemplateService($templateService)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setOrderTagManager($orderTagManager)
            ->setInvoiceService($invoiceService);
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_MAPPING);
    }

    public function saveMappingAction()
    {
        $entity = $this->getInvoiceService()->saveSettings(
            $this->params()->fromPost()
        );
        return $this->getJsonModelFactory()->newInstance([
            "invoiceSettings" => json_encode($entity),
        ]);
    }

    public function mappingAction()
    {
        $invoiceSettings = $this->getInvoiceService()->getSettings();
        $tradingCompanies = $this->getInvoiceService()->getTradingCompanies();
        $invoices = $this->getInvoiceService()->getInvoices();

        $view = $this->getViewModelFactory()->newInstance()
            ->setVariable('invoiceSettings', $invoiceSettings)
            ->setVariable('tradingCompanies', $tradingCompanies)
            ->setVariable('invoices', $invoices);
        return $view;
    }

    public function designAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->addChild($this->getTemplateSelectView(), 'templates');
        $view->addChild($this->getTemplateAddButtonView(), 'templateAddButton');
        $view->addChild($this->getTemplateDuplicateButtonView(), 'templateDuplicateButton');
        $view->addChild($this->getTemplateDiscardButtonView(), 'templateDiscardButton');
        $view->addChild($this->getTemplateSaveButtonView(), 'templateSaveButton');
        $view->addChild($this->getTemplateNameInputView(), 'templateName');

        $rootOu = $this->getUserOrganisationUnitService()->getRootOuByActiveUser();
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('templateSelectorId', static::TEMPLATE_SELECTOR_ID);
        $view->setVariable('paperTypeDropdownId', static::PAPER_TYPE_DROPDOWN_ID);

        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');

        $view->setVariable('dataFieldOptions', $this->getOrderTagManager()->getAvailableTags());

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
        $templateView->setVariable('initialTitle', $this->translate('Select Template'));
        $templateView->setVariable('id', static::TEMPLATE_SELECTOR_ID);
        return $templateView;
    }

    protected function getTemplateAddButtonView()
    {
        return $this->getButtonFromNameAndId($this->translate('New Template'), 'new-template', false);
    }

    protected function getTemplateDuplicateButtonView()
    {
        return $this->getButtonFromNameAndId($this->translate('Duplicate'), 'duplicate-template', true);
    }

    protected function getTemplateDiscardButtonView()
    {
        return $this->getButtonFromNameAndId($this->translate('Discard'), 'discard-template-button', false);
    }

    protected function getTemplateSaveButtonView()
    {
        return $this->getButtonFromNameAndId($this->translate('Save'), 'save-template-button', false);
    }

    protected function getButtonFromNameAndId($name, $id, $disabled)
    {
        $button = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $name,
            'id' => $id,
            'disabled' => $disabled
        ]);
        $button->setTemplate('elements/buttons.mustache');
        return $button;
    }

    protected function getTemplateNameInputView()
    {
        $input = $this->getViewModelFactory()->newInstance([
            'name' => 'template-name',
            'id' => 'template-name'
        ]);
        $input->setTemplate('elements/text.mustache');
        return $input;
    }

    public function fetchAction()
    {
        $template = $this->getTemplateService()->fetchAsJson($this->params()->fromPost('id'));
        $view = $this->getJsonModelFactory()->newInstance(["template" => $template]);
        return $view;
    }

    public function saveAction()
    {
        $template = $this->getTemplateService()->saveFromJson($this->params()->fromPost('template'));
        $view = $this->getJsonModelFactory()->newInstance(["template" => json_encode($template)]);
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

    public function getOrderTagManager()
    {
        return $this->orderTagManager;
    }

    public function setOrderTagManager(OrderTagManager $orderTagManager)
    {
        $this->orderTagManager = $orderTagManager;
        return $this;
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
            "id" => static::PAPER_TYPE_DROPDOWN_ID,
            "name" => static::PAPER_TYPE_DROPDOWN_ID,
            "class" => "",
            "options" => []
        ];

        $paperTypeModule = $this->getViewModelFactory()->newInstance($dropDownConfig);
        $paperTypeModule->setTemplate('InvoiceDesigner/Template/paperType');

        return $paperTypeModule;
    }

    public function getInvoiceService()
    {
        return $this->invoiceService;
    }

    public function setInvoiceService(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        return $this;
    }
}
