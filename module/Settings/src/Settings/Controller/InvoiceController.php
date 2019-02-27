<?php
namespace Settings\Controller;

use CG\Account\Shared\Filter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Credentials as AmazonCredentials;
use CG\Amazon\RegionAbstract as AmazonRegion;
use CG\Amazon\RegionFactory as AmazonRegionFactory;
use CG\Channel\Type as ChannelType;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Company\Service as IntercomCompanyService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\ReplaceManager\OrderContent as OrderTagManager;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Invoice\Helper as InvoiceHelper;
use Settings\Invoice\Mapper as InvoiceMapper;
use Settings\Invoice\Settings as InvoiceSettings;
use Settings\Invoice\Mappings as InvoiceMappings;
use Settings\Module;
use Zend\Config\Config;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class InvoiceController extends AbstractActionController implements LoggerAwareInterface
{
    use ExceptionToViewModelUserExceptionTrait;
    use LogTrait;

    const ROUTE = 'Invoice';
    const ROUTE_SETTINGS = 'Invoice Settings';
    const ROUTE_DESIGNER = 'Invoice Designer';
    const ROUTE_DESIGNER_ID = 'Invoice Designer View';
    const ROUTE_TEMPLATES = 'Invoice Templates';
    const ROUTE_TEMPLATES_NEW = 'Invoice Templates New';
    const ROUTE_TEMPLATES_EXISTING = 'Invoice Templates Existing';
    const ROUTE_AJAX = 'Ajax';
    const ROUTE_AJAX_MAPPING = 'Ajax Mapping';
    const ROUTE_FETCH = 'Fetch';
    const ROUTE_SAVE = 'Save';
    const ROUTE_SAVE_MAPPING = 'Save Mapping';
    const ROUTE_VERIFY = 'Verify';
    const TEMPLATE_SELECTOR_ID = 'template-selector';
    const PAPER_TYPE_DROPDOWN_ID = "paper-type-dropdown";

    const EVENT_SAVED_INVOICE_CHANGES = 'Saved Invoice Changes';

    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var TemplateService $templateService */
    protected $templateService;
    /** @var UserOrganisationUnitService $userOrganisationUnitService */
    protected $userOrganisationUnitService;
    /** @var OrderTagManager $orderTagManager */
    protected $orderTagManager;
    /** @var InvoiceHelper $invoiceHelper */
    protected $invoiceHelper;
    /** @var InvoiceSettings $invoiceSettings */
    protected $invoiceSettings;
    /** @var InvoiceMappings $invoiceMappings */
    protected $invoiceMappings;
    /** @var InvoiceMapper $invoiceMapper */
    protected $invoiceMapper;
    /** @var Translator $translator */
    protected $translator;
    /** @var Config $config */
    protected $config;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var IntercomCompanyService $intercomCompanyService */
    protected $intercomCompanyService;
    /** @var Cryptor $amazonCryptor */
    protected $amazonCryptor;
    /** @var AmazonRegionFactory $amazonRegionFactory */
    protected $amazonRegionFactory;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        OrderTagManager $orderTagManager,
        InvoiceHelper $invoiceHelper,
        InvoiceSettings $invoiceSettings,
        InvoiceMappings $invoiceMappings,
        InvoiceMapper $invoiceMapper,
        Translator $translator,
        Config $config,
        AccountService $accountService,
        IntercomEventService $intercomEventService,
        IntercomCompanyService $intercomCompanyService,
        Cryptor $amazonCryptor,
        AmazonRegionFactory $amazonRegionFactory
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->templateService = $templateService;
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        $this->orderTagManager = $orderTagManager;
        $this->invoiceHelper = $invoiceHelper;
        $this->invoiceSettings = $invoiceSettings;
        $this->invoiceMappings = $invoiceMappings;
        $this->invoiceMapper = $invoiceMapper;
        $this->translator = $translator;
        $this->config = $config;
        $this->accountService = $accountService;
        $this->intercomEventService = $intercomEventService;
        $this->intercomCompanyService = $intercomCompanyService;
        $this->amazonCryptor = $amazonCryptor;
        $this->amazonRegionFactory = $amazonRegionFactory;
    }

    public function indexAction()
    {
        $invoiceSettings = $this->invoiceSettings->getSettings();
        $existingInvoices = $this->invoiceSettings->getExistingInvoicesForView();

        return $this->viewModelFactory->newInstance()
            ->setVariable('invoiceSettings', $invoiceSettings)
            ->setVariable('invoiceData', json_encode($existingInvoices))
            ->setVariable('eTag', $invoiceSettings->getStoredETag())
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true);
    }

    public function saveSettingsAction()
    {
        $entity = $this->invoiceSettings->saveSettingsFromPostData($this->params()->fromPost());
        $emailVerificationStatus = $this->invoiceSettings->getEmailVerificationStatusFromEntity($entity);

        return $this->jsonModelFactory->newInstance([
            'invoiceSettings' => json_encode($entity),
            'emailVerifiedStatus' => $emailVerificationStatus,
            'eTag' => $entity->getStoredETag()
        ]);
    }

    public function ajaxSettingsAction()
    {
        $invoiceSettings = $this->invoiceSettings->getSettings();
        $tradingCompanies = $this->invoiceHelper->getTradingCompanies();
        $invoices = $this->invoiceSettings->getInvoices();

        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];

        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = count($tradingCompanies);
        foreach ($tradingCompanies as $tradingCompany) {
            $data['Records'][] = $this->invoiceMapper->toDataTableArray(
                $tradingCompany,
                $invoices,
                $invoiceSettings
            );
        }
        return $this->jsonModelFactory->newInstance($data);
    }

    public function saveMappingAction()
    {
        $this->invoiceMappings->saveInvoiceMappingFromPostData($this->params()->fromPost());
        return $this->getJsonModelFactory()->newInstance([
            'invoiceMapping' => true
        ]);
    }

    public function ajaxMappingAction()
    {
        $invoices = $this->invoiceSettings->getInvoices();
        $ouIds = $this->userOrganisationUnitService->getAncestorOrganisationUnitIdsByActiveUser();

        $filter = (new Filter())
            ->setOrganisationUnitId($ouIds)
            ->setDeleted(0)
            ->setType(ChannelType::SALES)
            ->setLimit("all");
        $accounts = $this->accountService->fetchByFilter($filter, true);

        $this->logDebugDump($accounts, 'ACCOUNTS', [], 'MYTEST');

        $dataTablesData = $this->invoiceMappings->getInvoiceMappingDataTablesData($accounts, $invoices);

        $this->logDebugDump($dataTablesData, 'dataTablesData', [], 'MYTEST');

        $data = [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => $dataTablesData,
        ];

        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) count($dataTablesData);
        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function settingsAction()
    {
        $invoiceSettings = $this->invoiceSettings->getSettings();
        $tradingCompanies = $this->invoiceHelper->getTradingCompanies();
        $invoices = $this->invoiceSettings->getInvoices();

        $view = $this->viewModelFactory->newInstance()
            ->setVariable('invoiceSettings', $invoiceSettings)
            ->setVariable('tradingCompanies', $tradingCompanies)
            ->setVariable('invoices', $invoices)
            ->setVariable('eTag', $invoiceSettings->getStoredETag())
            ->setVariable('amazonSite', $this->getUserAmazonAccountSite())
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('emailVerified', $invoiceSettings->isEmailVerified())
            ->setVariable('emailSendAs', $invoiceSettings->getEmailSendAs())
            ->setVariable('emailTemplate', $invoiceSettings->getEmailTemplate())
            ->setVariable('tagOptions', $this->orderTagManager->getAvailableTags())
            ->addChild($this->getInvoiceSettingsItemSkuCheckboxView($invoiceSettings), 'itemSkuCheckbox')
            ->addChild($this->getInvoiceSettingsProductImagesCheckboxView($invoiceSettings), 'productImagesCheckbox')
            ->addChild($this->getInvoiceSettingsItemBarcodesCheckboxView($invoiceSettings), 'itemBarcodesCheckbox')
            ->addChild($this->getInvoiceSettingsItemVariationAttributesCheckboxView($invoiceSettings), 'itemVariationAttributesCheckbox')
            ->addChild($this->getInvoiceSettingsEmailSendAsView($invoiceSettings), 'emailSendAsInput')
            ->addChild($this->getInvoiceSettingsCopyRequiredView($invoiceSettings), 'copyRequiredCheckbox')
            ->addChild($this->getInvoiceSettingsEmailBccView($invoiceSettings), 'emailBccInput')
            ->addChild($this->getTradingCompanyInvoiceSettingsDataTable(), 'invoiceSettingsDataTable')
            ->addChild($this->getInvoiceMappingTable(), 'invoiceMappingTable');

        if ($invoiceSettings->getEmailSendAs()) {
            $view->addChild($this->getInvoiceEmailVerificationStatusView($invoiceSettings), 'emailVerificationStatus');
        }

        return $view;
    }

    public function designAction()
    {
        $showToPdfButton = $this->config->get('CG')->get('Settings')->get('show_to_pdf_button');

        $view = $this->viewModelFactory->newInstance();

        $template = $this->params()->fromRoute('templateId');
        $view->setVariable("templateId", $template);

        $view->addChild($this->getTemplateDiscardButtonView(), 'templateDiscardButton');
        $view->addChild($this->getTemplateSaveButtonView(), 'templateSaveButton');
        $view->addChild($this->getTemplateNameInputView(), 'templateName');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);

        $rootOu = $this->userOrganisationUnitService->getRootOuByActiveUser();
        $view->setVariable('rootOuId', $rootOu->getId());
        $view->setVariable('templateSelectorId', static::TEMPLATE_SELECTOR_ID);
        $view->setVariable('paperTypeDropdownId', static::PAPER_TYPE_DROPDOWN_ID);
        $view->setVariable('showToPdfButton', $showToPdfButton);

        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');

        $view->setVariable('dataFieldOptions', $this->orderTagManager->getAvailableTags());

        return $view;
    }

    public function fetchAction()
    {
        $template = $this->templateService->fetchAsJson($this->params()->fromPost('id'));
        $view = $this->jsonModelFactory->newInstance(["template" => $template]);
        return $view;
    }

    public function saveAction()
    {
        try{
            $template = $this->templateService->saveFromJson($this->params()->fromPost('template'));
            $this->notifyOfSave();
            $view = $this->jsonModelFactory->newInstance(["template" => json_encode($template)]);
            return $view;
        } catch (NotModified $e) {
            throw $this->exceptionToViewModelUserException($e, 'There were no changes to be saved');
        } catch (\Exception $e) {
            $this->logException($e, 'log:error', __NAMESPACE__);
            throw $this->exceptionToViewModelUserException($e, 'Template could not be saved.');
        }
        return false;
    }

    public function getUserAmazonAccountSite(){
        try {
            $filter = (new Filter())
                ->setOrganisationUnitId($this->userOrganisationUnitService->getAncestorOrganisationUnitIdsByActiveUser())
                ->setChannel(["amazon"])
                ->setLimit("all");

            $accounts = $this->accountService->fetchByFilter($filter);

            /** @var Account $account */
            foreach ($accounts as $account) {
                /** @var AmazonCredentials $credentials */
                $credentials = $this->amazonCryptor->decrypt($account->getCredentials());
                /** @var AmazonRegion $region */
                $region = $this->amazonRegionFactory->getByRegionCode($credentials->getRegionCode());

                $domains = $region->getDomains();
                if (!empty($domains)) {
                    return reset($domains);
                }
            }
        } catch (NotFound $exception) {
            // NoOp
        }
        return '';
    }

    protected function notifyOfSave()
    {
        $activeUser = $this->userOrganisationUnitService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_SAVED_INVOICE_CHANGES, $activeUser->getId());
        $this->intercomEventService->save($event);
    }

    protected function getInvoiceEmailVerificationStatusView(InvoiceSettingsEntity $invoiceSettings)
    {
        $config = $this->invoiceSettings->getEmailVerificationStatusForDisplay($invoiceSettings->getEmailVerificationStatus());
        return $this->viewModelFactory->newInstance($config)->setTemplate('elements/status.mustache');
    }

    protected function getTradingCompanyInvoiceSettingsDataTable()
    {
        $datatables = $this->invoiceSettings->getDatatable();
        $settings = $datatables->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SETTINGS.'/'.static::ROUTE_AJAX
            )
        );
        $settings->setTemplateUrlMap([
            'tradingCompany' => '/channelgrabber/settings/template/columns/tradingCompany.mustache',
            'assignedInvoice' => \CG_UI\Module::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
            'sendFromAddress' => '/channelgrabber/settings/template/columns/sendFromAddress.mustache',
        ]);
        return $datatables;
    }

    protected function getInvoiceMappingTable()
    {
        $datatables = $this->invoiceMappings->getDatatable();
        $settings = $datatables->getVariable('settings');

        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SETTINGS.'/'.static::ROUTE_AJAX_MAPPING
            )
        );
        $settings->setTemplateUrlMap([
            'channel' => '/channelgrabber/settings/template/columns/channel.mustache',
            'displayName' => '/channelgrabber/settings/template/columns/account.mustache',
            'site' => '/channelgrabber/settings/template/columns/site.mustache',
            'tradingCompany' => '/channelgrabber/settings/template/columns/tradingCompanySelect.mustache',
            'assignedInvoice' => '/channelgrabber/settings/template/columns/assignedInvoice.mustache',
            'sendViaEmail' => '/channelgrabber/settings/template/columns/sendViaEmail.mustache',
            'sendToFba' => '/channelgrabber/settings/template/columns/sendToFba.mustache',
            'customSelect' => \CG_UI\Module::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
            'enable' => '/channelgrabber/settings/template/columns/enable.mustache',
            'emailContent' => '/channelgrabber/settings/template/columns/emailContent.mustache'
        ]);
        return $datatables;
    }

    protected function getInvoiceSettingsDefaultSelectView(InvoiceSettingsEntity $invoiceSettings, $invoices)
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
        return $this->viewModelFactory->newInstance($customSelectConfig)
            ->setTemplate('elements/custom-select.mustache');
    }

    protected function getInvoiceSettingsAutoEmailToggleView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'autoEmail',
                    'name' => 'autoEmail',
                    'selected' => (boolean) $invoiceSettings->getAutoEmail(),
                ]
            )
            ->setTemplate('elements/toggle.mustache');
    }

    protected function getInvoiceSettingsSendToFbaToggleView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'sendToFbaDefault',
                    'name' => 'sendToFbaDefault',
                    'selected' => (boolean) $invoiceSettings->getSendToFba(),
                ]
            )
            ->setTemplate('elements/toggle.mustache');
    }

    protected function getInvoiceSettingsItemSkuCheckboxView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'itemSku',
                    'name' => 'itemSku',
                    'selected' => (bool) $invoiceSettings->getItemSku(),
                ]
            )
            ->setTemplate('elements/checkbox.mustache');
    }

    protected function getInvoiceSettingsProductImagesCheckboxView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'productImages',
                    'name' => 'productImages',
                    'selected' => $invoiceSettings->getProductImages(),
                ]
            )
            ->setTemplate('elements/checkbox.mustache');
    }

    protected function getInvoiceSettingsItemBarcodesCheckboxView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'itemBarcodes',
                    'name' => 'itemBarcodes',
                    'selected' => $invoiceSettings->getItemBarcodes(),
                ]
            )
            ->setTemplate('elements/checkbox.mustache');
    }

    protected function getInvoiceSettingsItemVariationAttributesCheckboxView(InvoiceSettingsEntity $invoiceSettings): ViewModel
    {
        return $this->viewModelFactory
            ->newInstance(
                [
                    'id' => 'itemVariationAttributes',
                    'name' => 'itemVariationAttributes',
                    'selected' => $invoiceSettings->getItemVariationAttributes(),
                ]
            )
            ->setTemplate('elements/checkbox.mustache');
    }

    protected function getInvoiceSettingsEmailSendAsView(InvoiceSettingsEntity $invoiceSettings)
    {
        return $this->viewModelFactory
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

	protected function getInvoiceSettingsCopyRequiredView(InvoiceSettingsEntity $invoiceSettings)
	{
		return $this->viewModelFactory
			->newInstance(
				[
					'id' => 'copyRequired',
					'name' => 'copyRequired',
					'selected' => $invoiceSettings->isCopyRequired(),
				]
			)
			->setTemplate('elements/checkbox.mustache');
	}

	protected function getInvoiceSettingsEmailBccView(InvoiceSettingsEntity $invoiceSettings)
	{
		return $this->viewModelFactory
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
        $organisationUnitIds = $this->userOrganisationUnitService->getAncestorOrganisationUnitIdsByActiveUser();
        $templates = $this->templateService->fetchInvoiceCollectionByOrganisationUnitWithHardCoded($organisationUnitIds);
        $options = [];
        foreach ($templates as $template) {
            $options[] = [
                "title" => $template->getName(),
                "value" => $template->getId(),
            ];
        }
        $templateView = $this->viewModelFactory->newInstance(["options" => $options]);
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
        $button = $this->viewModelFactory->newInstance([
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
        $input = $this->viewModelFactory->newInstance([
            'name' => 'template-name',
            'id' => 'template-name'
        ]);
        $input->setTemplate('elements/text.mustache');
        return $input;
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

        $paperTypeModule = $this->viewModelFactory->newInstance();
        $select = $this->viewModelFactory->newInstance($dropDownConfig);
        $select->setTemplate('elements/custom-select.mustache');
        $paperTypeModule->addChild($select, 'select');
        $paperTypeModule->setTemplate('InvoiceDesigner/Template/paperType');

        return $paperTypeModule;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->translator;
    }
}
