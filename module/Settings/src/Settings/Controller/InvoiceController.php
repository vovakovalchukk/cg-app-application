<?php
namespace Settings\Controller;

use CG\Amazon\Aws\Ses\Service as AmazonSesService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Company\Service as IntercomCompanyService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Stdlib\DateTime;
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
    const EVENT_SAVED_INVOICE_CHANGES = 'Saved Invoice Changes';
    const EVENT_EMAIL_INVOICE_CHANGES = 'Enable/Disable Email Invoice';

    protected $viewModelFactory;
    protected $jsonModelFactory;
    protected $templateService;
    protected $userOrganisationUnitService;
    protected $orderTagManager;
    protected $invoiceService;
    protected $invoiceMapper;
    protected $translator;
    protected $config;
    protected $intercomEventService;
    protected $intercomCompanyService;
    protected $accountService;
    protected $filter;
    protected $amazonSesService;

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
        IntercomEventService $intercomEventService,
        IntercomCompanyService $intercomCompanyService,
        AccountService $accountService,
        AmazonSesService $amazonSesService
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
            ->setIntercomEventService($intercomEventService)
            ->setIntercomCompanyService($intercomCompanyService)
            ->setAccountService($accountService);
		$this->amazonSesService = $amazonSesService;
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
        $data = $this->params()->fromPost();
        $invoiceSettings = $this->invoiceService->getSettings();

        try {
            $autoEmail = $invoiceSettings->getAutoEmail();
        } catch (NotFound $e) {
            $autoEmail = false;
        }

        try {

            $data = $this->validateEmailSendAs($data);
            $data = $this->validateAutoEmail($data);
            $data = $this->validateProductImages($data);
			$data = $this->handleAutoEmailChange($autoEmail, $data);

			$emailSendAs = $data['emailSendAs'];
			$data['emailVerified'] = $this->amazonSesService->getVerificationStatus($emailSendAs);

			// If email is not verified and the address has changed, we need to submit a new verification request to SES.
            if (! $data['emailVerified'] && $this->hasEmailChanged($emailSendAs, $invoiceSettings->getEmailSendAs())) {
                $data['emailVerified'] = $this->handleEmailVerification($emailSendAs);
            }

            if (! empty($data['tradingCompanies'])) {
                $data['tradingCompanies'] = $this->handleTradingCompanyEmailVerification($data['tradingCompanies'], $invoiceSettings->getTradingCompanies());
            }

            $settings = array_merge($invoiceSettings->toArray(), $data);
            $entity = $this->invoiceService->saveSettings($settings);

        } catch (NotModified $e) {
            // display saved message
            $entity = $this->invoiceService->getSettings();
        }

        $etag = $entity->getStoredETag();
        $entity = array_merge($entity->toArray(), ['emailVerifiedStatus' => $this->setEmailVerifiedStatus($entity->isEmailVerified())]);

		if (!empty($entity['tradingCompanies'])) {
			foreach ($entity['tradingCompanies'] as $key => $tradingCompany) {
				$entity['tradingCompanies'][$key]['emailVerifiedStatus'] = $this->setEmailVerifiedStatus($tradingCompany['emailVerified']);
			}
		}

        return $this->getJsonModelFactory()->newInstance([
            'invoiceSettings' => json_encode($entity),
            'eTag' => $etag
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

    protected function notifyOfSave()
    {
        $activeUser = $this->getUserOrganisationUnitService()->getActiveUser();
        $event = new IntercomEvent(static::EVENT_SAVED_INVOICE_CHANGES, $activeUser->getId());
        $this->intercomEventService->save($event);
    }

    protected function notifyOfAutoEmailChange($enabled)
    {
        $activeUser = $this->getUserOrganisationUnitService()->getActiveUser();
        $event = new IntercomEvent(static::EVENT_EMAIL_INVOICE_CHANGES, $activeUser->getId(), ['email-invoice' => (boolean) $enabled]);
        $this->intercomEventService->save($event);
        $this->intercomCompanyService->save($this->getUserOrganisationUnitService()->getRootOuByUserEntity($activeUser));
    }

	/**
	 * @param $data
	 * @return mixed
	 */
	protected function validateEmailSendAs($data)
	{
		if (isset($data['emailSendAs'])) {
			$data['emailSendAs'] = filter_var($data['emailSendAs'], FILTER_VALIDATE_EMAIL) ? $data['emailSendAs'] : null;
		}

		return $data;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	protected function validateAutoEmail($data)
	{
		if (isset($data['autoEmail'])) {
			$data['autoEmail'] = filter_var($data['autoEmail'], FILTER_VALIDATE_BOOLEAN);
		}

		return $data;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	protected function validateProductImages($data)
	{
		if (isset($data['productImages'])) {
			$data['productImages'] = filter_var($data['productImages'], FILTER_VALIDATE_BOOLEAN);
		}

		return $data;
	}

	/**
	 * @param $emailSendAs
	 * @param $invoiceSettingsEmailSendAs
	 * @return bool
	 */
	protected function hasEmailChanged($emailSendAs, $invoiceSettingsEmailSendAs)
	{
		return $emailSendAs !== $invoiceSettingsEmailSendAs;
	}

	/**
	 * @param $autoEmail
	 * @param $data
	 * @return mixed
	 */
	protected function handleAutoEmailChange($autoEmail, $data)
	{
		if ($autoEmail && $data['autoEmail']) {
			$data['autoEmail'] = $autoEmail;
			// Value unchanged so don't tell intercom
		} else if ($data['autoEmail']) {
			$data['autoEmail'] = (new DateTime())->stdFormat();
			$this->notifyOfAutoEmailChange(true);
		} else {
			$data['autoEmail'] = null;
			$this->notifyOfAutoEmailChange(false);
		}

		return $data;
	}

	/**
	 * @param $email
	 * @return bool
	 */
	protected function handleEmailVerification($email)
	{
		$emailVerified = $this->amazonSesService->getVerificationStatus($email);

		if (!$emailVerified) {
			$this->amazonSesService->verifyEmailIdentity($email);
		}

		return $emailVerified;
	}

	/**
	 * @param array $tradingCompanies
	 * @param array $invoiceSettingsTradingCompanies
	 * @return array
	 */
	protected function handleTradingCompanyEmailVerification(array $tradingCompanies, array $invoiceSettingsTradingCompanies)
	{
		foreach ($tradingCompanies as $key => $value) {
			$tradingCompany = $this->validateEmailSendAs($tradingCompanies[$key]);

			$emailSendAs = $tradingCompany['emailSendAs'];
			$emailVerified = isset($invoiceSettingsTradingCompanies[$key]['emailVerified']) ? $invoiceSettingsTradingCompanies[$key]['emailVerified'] : false;
			$invoiceSettingsEmailSendAs = isset($invoiceSettingsTradingCompanies[$key]['emailSendAs']) ? $invoiceSettingsTradingCompanies[$key]['emailSendAs'] : null;

			if ($this->hasEmailChanged($emailSendAs, $invoiceSettingsEmailSendAs)) {
				$emailVerified = $this->handleEmailVerification($emailSendAs);
			}

			$tradingCompany['emailVerified'] = $emailVerified;
			$tradingCompanies[$key] = $tradingCompany;
		}

		return $tradingCompanies;
	}

	/**
	 * @param $emailVerified
	 * @return array
	 */
	protected function setEmailVerifiedStatus($emailVerified)
	{
		$active = ['status' => 'active', 'class' => 'email-verify-status', 'message' => AmazonSesService::STATUS_MESSAGE_VERIFIED];
		$pending = ['status' => 'pending', 'class' => 'email-verify-status', 'message' => AmazonSesService::STATUS_MESSAGE_PENDING];
		return $emailVerified ? $active : $pending ;
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

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    protected function setIntercomCompanyService(IntercomCompanyService $intercomCompanyService)
    {
        $this->intercomCompanyService = $intercomCompanyService;
        return $this;
    }

	protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }


}