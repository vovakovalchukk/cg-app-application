<?php
namespace Settings\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Config\Config;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Module;
use Settings\Invoice\Service as InvoiceService;
use Settings\Invoice\Mapper as InvoiceMapper;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Template\ReplaceManager\OrderContent as OrderTagManager;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Zend\Stdlib\View\Model\Exception as ViewModelException;
use Zend\I18n\Translator\Translator;
use CG\Stdlib\Log\LogTrait;

class InvoiceController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE = 'Invoice';
    const ROUTE_MAPPING = 'Invoice Mapping';
    const ROUTE_DESIGNER = 'Invoice Designer';
    const ROUTE_AJAX = 'Ajax';
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
    protected $invoiceMapper;
    protected $translator;
    protected $config;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        OrderTagManager $orderTagManager,
        InvoiceService $invoiceService,
        InvoiceMapper $invoiceMapper,
        Translator $translator,
        Config $config
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setTemplateService($templateService)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setOrderTagManager($orderTagManager)
            ->setInvoiceService($invoiceService)
            ->setInvoiceMapper($invoiceMapper)
            ->setTranslator($translator)
            ->setConfig($config);
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


    public function ajaxMappingAction()
    {
        $invoiceSettings = $this->getInvoiceService()->getSettings();
        $tradingCompanies = $this->getInvoiceService()->getTradingCompanies();
        $invoices = $this->getInvoiceService()->getInvoices();

        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];

        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $tradingCompanies->count();

        foreach ($tradingCompanies as $tradingCompany) {
            $data['Records'][] = $this->getInvoiceMapper()->toDataTableArray(
                $tradingCompany,
                $invoices,
                $invoiceSettings
            );
        }
        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function mappingAction()
    {
        $invoiceSettings = $this->getInvoiceService()->getSettings();
        $tradingCompanies = $this->getInvoiceService()->getTradingCompanies();
        $invoices = $this->getInvoiceService()->getInvoices();

        $view = $this->getViewModelFactory()->newInstance()
            ->setVariable('invoiceSettings', $invoiceSettings)
            ->setVariable('tradingCompanies', $tradingCompanies)
            ->setVariable('invoices', $invoices)
            ->addChild($this->getInvoiceSettingsDefaultSelectView($invoiceSettings, $invoices), 'defaultCustomSelect')
            ->addChild($this->getTradingCompanyInvoiceSettingsDataTable(), 'invoiceSettingsDataTable');
        return $view;
    }

    protected function getTradingCompanyInvoiceSettingsDataTable()
    {
        $datatables = $this->getInvoiceService()->getDatatable();
        $settings = $datatables->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_MAPPING.'/'.static::ROUTE_AJAX
            )
        );
        $settings->setTemplateUrlMap([
            'tradingCompany' => '/channelgrabber/settings/template/columns/tradingCompany.html',
            'assignedInvoice' => \CG_UI\Module::PUBLIC_FOLDER . '/templates/elements/custom-select.mustache',
        ]);
        return $datatables;
    }

    public function designAction()
    {
        $showToPdfButton = $this->getConfig()->get('CG')->get('Settings')->get('show_to_pdf_button');

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
        $view->setVariable('showToPdfButton', $showToPdfButton);

        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');

        $view->setVariable('dataFieldOptions', $this->getOrderTagManager()->getAvailableTags());

        return $view;
    }

    protected function getInvoiceSettingsDefaultSelectView($invoiceSettings, $invoices)
    {
        $customSelectConfig['name'] = 'defaultInvoiceCustomSelect';
        $customSelectConfig['id'] = $customSelectConfig['name'];
        foreach ($invoices as $invoice) {
            $customSelectConfig['options'][] = [
                'title' => $invoice->getName(),
                'value' => $invoice->getId(),
                'selected' => ($invoice->getId() == $invoiceSettings->getDefault())
            ];
        };
        return $this->getViewModelFactory()->newInstance($customSelectConfig)
                                            ->setTemplate('elements/custom-select.mustache');
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
        try{
            $template = $this->getTemplateService()->saveFromJson($this->params()->fromPost('template'));
            $view = $this->getJsonModelFactory()->newInstance(["template" => json_encode($template)]);
            return $view;
        } catch (NotModified $e) {
            $this->handleAccountUpdateException($e, 'There were no changes to be saved');
        } catch (Exception $e) {
            $this->handleAccountUpdateException($e, 'Template could not be saved.');
            $this->logException($e, 'log:error', __NAMESPACE__);
        }
        return false;
    }

    protected function handleAccountUpdateException(\Exception $e, $message)
    {
        $status = $this->getJsonModelFactory()->newInstance();
        $status->setVariable('valid', false);
        throw new ViewModelException(
            $status,
            $this->getTranslator()->translate($message),
            $e->getCode(),
            $e
        );
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

        $paperTypeModule = $this->getViewModelFactory()->newInstance();
        $select = $this->getViewModelFactory()->newInstance($dropDownConfig);
        $select->setTemplate('elements/custom-select.mustache');
        $paperTypeModule->addChild($select, 'select');
        $paperTypeModule->setTemplate('InvoiceDesigner/Template/paperType');

        return $paperTypeModule;
    }

    /**
     * @return InvoiceService
     */
    protected function getInvoiceService()
    {
        return $this->invoiceService;
    }

    public function setInvoiceService(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        return $this;
    }

    /**
     * @return InvoiceMapper
     */
    public function getInvoiceMapper()
    {
        return $this->invoiceMapper;
    }

    public function setInvoiceMapper(InvoiceMapper $invoiceMapper)
    {
        $this->invoiceMapper = $invoiceMapper;
        return $this;
    }

    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
    
    protected function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }
}
