<?php
namespace Settings\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\ReplaceManager\OrderContent as OrderTagManager;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Invoice\Mapper as InvoiceMapper;
use Settings\Invoice\Service as InvoiceService;
use Settings\Module;
use Zend\Config\Config;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Filter;

class InvoiceController extends AbstractActionController implements LoggerAwareInterface
{
    use ExceptionToViewModelUserExceptionTrait;
    use LogTrait;

    const ROUTE = 'Invoice';
    const ROUTE_MAPPING = 'Invoice Mapping';
    const ROUTE_DESIGNER = 'Invoice Designer';
    const ROUTE_DESIGNER_ID = 'Invoice Designer View';
    const ROUTE_TEMPLATES = 'Invoice Templates';
    const ROUTE_TEMPLATES_NEW = 'Invoice Templates New';
    const ROUTE_TEMPLATES_EXISTING = 'Invoice Templates Existing';
    const ROUTE_AJAX = 'Ajax';
    const ROUTE_FETCH = 'Fetch';
    const ROUTE_SAVE = 'Save';
    const ROUTE_VERIFY = 'Verify';
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
    protected $accountService;
    protected $filter;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        OrderTagManager $orderTagManager,
        InvoiceService $invoiceService,
        InvoiceMapper $invoiceMapper,
        Translator $translator,
        Config $config,
        AccountService $accountService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setTemplateService($templateService)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setOrderTagManager($orderTagManager)
            ->setInvoiceService($invoiceService)
            ->setInvoiceMapper($invoiceMapper)
            ->setTranslator($translator)
            ->setConfig($config)
            ->setAccountService($accountService);
    }

    public function indexAction()
    {
        $invoiceSettings = $this->invoiceService->getSettings();
        $existingInvoices = $this->invoiceService->getExistingInvoicesForView();

        return $this->getViewModelFactory()->newInstance()
					->setVariable('invoiceSettings', $invoiceSettings)
					->setVariable('invoiceData', json_encode($existingInvoices))
					->setVariable('eTag', $invoiceSettings->getStoredETag())
					->setVariable('isHeaderBarVisible', false)
					->setVariable('subHeaderHide', true);
    }

    public function saveMappingAction()
    {
        $entity = $this->invoiceService->saveSettingsFromPostData($this->params()->fromPost());
        $emailVerifiedStatus = $this->invoiceService->getEmailVerifiedStatusFromEntity($entity);

        return $this->getJsonModelFactory()->newInstance([
            'invoiceSettings' => json_encode($entity),
            'emailVerifiedStatus' => $emailVerifiedStatus,
            'eTag' => $entity->getStoredETag()
        ]);
    }

    public function ajaxMappingAction()
    {
        $invoiceSettings = $this->invoiceService->getSettings();
        $tradingCompanies = $this->invoiceService->getTradingCompanies();
        $invoices = $this->invoiceService->getInvoices();

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
        $invoiceSettings = $this->invoiceService->getSettings();
        $tradingCompanies = $this->invoiceService->getTradingCompanies();
        $invoices = $this->invoiceService->getInvoices();

        return $this->getViewModelFactory()->newInstance()
					->setVariable('invoiceSettings', $invoiceSettings)
					->setVariable('tradingCompanies', $tradingCompanies)
					->setVariable('invoices', $invoices)
					->setVariable('eTag', $invoiceSettings->getStoredETag())
					->setVariable('hasAmazonAccount',$this->checkIfUserHasAmazonAccount())
					->setVariable('autoEmail', $invoiceSettings->getAutoEmail())
					->addChild($this->getInvoiceSettingsDefaultSelectView($invoiceSettings, $invoices), 'defaultCustomSelect')
					->addChild($this->getInvoiceSettingsAutoEmailToggleView($invoiceSettings), 'autoEmailToggle')
					->addChild($this->getInvoiceSettingsProductImagesCheckboxView($invoiceSettings), 'productImagesCheckbox')
					->addChild($this->getInvoiceSettingsEmailSendAsView($invoiceSettings), 'emailSendAsInput')
					->addChild($this->getInvoiceSettingsCopyRequiredView($invoiceSettings), 'copyRequiredCheckbox')
					->addChild($this->getInvoiceSettingsEmailBccView($invoiceSettings), 'emailBccInput')
					->addChild($this->getTradingCompanyInvoiceSettingsDataTable(), 'invoiceSettingsDataTable')
					->setVariable('isHeaderBarVisible', false)
					->setVariable('subHeaderHide', true)
					->setVariable('emailVerified', $invoiceSettings->isEmailVerified())
					->setVariable('emailSendAs', $invoiceSettings->getEmailSendAs());
    }

    public function designAction()
    {
        $showToPdfButton = $this->config->get('CG')->get('Settings')->get('show_to_pdf_button');

        $view = $this->getViewModelFactory()->newInstance();

        $template = $this->params()->fromRoute('templateId');
        $view->setVariable("templateId", $template);

        $view->addChild($this->getTemplateDiscardButtonView(), 'templateDiscardButton');
        $view->addChild($this->getTemplateSaveButtonView(), 'templateSaveButton');
        $view->addChild($this->getTemplateNameInputView(), 'templateName');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);

        $rootOu = $this->getUserOrganisationUnitService()->getRootOuByActiveUser();
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('templateSelectorId', static::TEMPLATE_SELECTOR_ID);
        $view->setVariable('paperTypeDropdownId', static::PAPER_TYPE_DROPDOWN_ID);
        $view->setVariable('showToPdfButton', $showToPdfButton);

        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');

        $view->setVariable('dataFieldOptions', $this->getOrderTagManager()->getAvailableTags());

        return $view;
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
            $this->notifyOfSave();
            $view = $this->getJsonModelFactory()->newInstance(["template" => json_encode($template)]);
            return $view;
        } catch (NotModified $e) {
            throw $this->exceptionToViewModelUserException($e, 'There were no changes to be saved');
        } catch (Exception $e) {
            throw $this->exceptionToViewModelUserException($e, 'Template could not be saved.');
            $this->logException($e, 'log:error', __NAMESPACE__);
        }
        return false;
    }

    public function checkIfUserHasAmazonAccount(){
        try {
            $filter = (new Filter())
                ->setOrganisationUnitId($this->userOrganisationUnitService->getAncestorOrganisationUnitIdsByActiveUser())
                ->setChannel(["amazon"])
                ->setLimit("all");
            if(!empty($this->accountService->fetchByFilter($filter))) {
                return true;
            }
        } catch (NotFound $exception) {
            return false;
        }
        return false;
    }

    protected function getTradingCompanyInvoiceSettingsDataTable()
    {
        $datatables = $this->invoiceService->getDatatable();
        $settings = $datatables->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_MAPPING.'/'.static::ROUTE_AJAX
            )
        );
        $settings->setTemplateUrlMap([
            'tradingCompany' => '/channelgrabber/settings/template/columns/tradingCompany.mustache',
            'assignedInvoice' => \CG_UI\Module::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
            'sendFromAddress' => '/channelgrabber/settings/template/columns/sendFromAddress.mustache',
        ]);
        return $datatables;
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

    protected function getInvoiceSettingsAutoEmailToggleView($invoiceSettings)
    {
        return $this->getViewModelFactory()
            ->newInstance(
                [
                    'id' => 'autoEmail',
                    'name' => 'autoEmail',
                    'selected' => (boolean) $invoiceSettings->getAutoEmail(),
                ]
            )
            ->setTemplate('elements/toggle.mustache');
    }

    protected function getInvoiceSettingsProductImagesCheckboxView($invoiceSettings)
    {
        return $this->getViewModelFactory()
            ->newInstance(
                [
                    'id' => 'productImages',
                    'name' => 'productImages',
                    'selected' => $invoiceSettings->getProductImages(),
                ]
            )
            ->setTemplate('elements/checkbox.mustache');
    }

    protected function getInvoiceSettingsEmailSendAsView($invoiceSettings)
    {
        return $this->getViewModelFactory()
            ->newInstance(
                [
                    'id' => 'emailSendAs',
                    'name' => 'emailSendAs',
                    'type' => 'email',
                    'value' => $invoiceSettings->getEmailSendAs(),
                    'class' => 'email-verify-input'
                ]
            )
            ->setTemplate('elements/text.mustache');
    }

	protected function getInvoiceSettingsCopyRequiredView($invoiceSettings)
	{
		return $this->getViewModelFactory()
			->newInstance(
				[
					'id' => 'copyRequired',
					'name' => 'copyRequired',
					'selected' => $invoiceSettings->isCopyRequired(),
				]
			)
			->setTemplate('elements/checkbox.mustache');
	}

	protected function getInvoiceSettingsEmailBccView($invoiceSettings)
	{
		return $this->getViewModelFactory()
			->newInstance(
				[
					'id' => 'emailBcc',
					'name' => 'emailBcc',
                    'type' => 'email',
					'placeholder' => 'Enter email to send to',
					'value' => $invoiceSettings->getEmailBcc(),
				]
			)
			->setTemplate('elements/text.mustache');
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
        $templateView->setVariable('disabled', true);
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

	protected function getTranslator()
	{
		return $this->translator;
	}

    /**
     * @return InvoiceMapper
     */
    public function getInvoiceMapper()
    {
        return $this->invoiceMapper;
    }

	public function getAccountService()
	{
		return $this->accountService;
	}

	public function setInvoiceService(InvoiceService $invoiceService)
	{
		$this->invoiceService = $invoiceService;
		return $this;
	}

    public function setInvoiceMapper(InvoiceMapper $invoiceMapper)
    {
        $this->invoiceMapper = $invoiceMapper;
        return $this;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    protected function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

	protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }
}