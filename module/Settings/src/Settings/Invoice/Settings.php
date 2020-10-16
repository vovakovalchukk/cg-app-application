<?php
namespace Settings\Invoice;

use CG\Amazon\Aws\Ses\Service as AmazonSesService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Company\Service as IntercomCompanyService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\Invoice\Shared\Mapper as InvoiceSettingsMapper;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\Collection as TemplateCollection;
use CG\Template\Entity as Template;
use CG\Template\Filter as TemplateFilter;
use CG\Template\Service as TemplateService;
use CG\Template\SystemTemplateEntity as SystemTemplate;
use CG\Template\Type as TemplateType;
use CG\User\ActiveUserInterface;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG_UI\View\DataTable;
use Settings\Module;

class Settings
{
    const TEMPLATE_THUMBNAIL_PATH = 'img/InvoiceOverview/TemplateThumbnails/';
    const EVENT_EMAIL_INVOICE_CHANGES = 'Enable/Disable Email Invoice';
    const SITE_DEFAULT = 'UK';

    /** @var Helper $helper */
    protected $helper;
    /** @var InvoiceSettingsService $invoiceSettingsService */
    protected $invoiceSettingsService;
    /** @var TemplateService $templateService */
    protected $templateService;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var InvoiceSettingsMapper $invoiceSettingsMapper */
    protected $invoiceSettingsMapper;
    /** @var DataTable $datatable */
    protected $datatable;
    /** @var AmazonSesService $amazonSesService */
    protected $amazonSesService;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var IntercomCompanyService $intercomCompanyService */
    protected $intercomCompanyService;
    /** @var UserOrganisationUnitService $userOrganisationUnitService */
    protected $userOrganisationUnitService;

    /** @var array $templateImagesMap */
    protected $templateImagesMap = [
        'FPS-3' => 'Form-FPS3.png',
        'FPS-15' => 'Form-FPS15.png',
        'FPS-16' => 'Form-FPS16.png',
        'FPD-1' => 'Form-FPD1.png',
        Template::DEFAULT_TEMPLATE_ID => 'blank.png',
    ];
    /** @var array $templatePurchaseLinksMap */
    protected $templatePurchaseLinksMap = [
        'FPS-3' => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link',
        'FPS-15' => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-15/?utm_source=Channel%20Grabber&utm_medium=Link&utm_campaign=FPS-15%20CG',
        'FPS-16' => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-16/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-16%20CG%20Link',
        'FPD-1' => 'https://www.formsplus.co.uk/online-shop/integrated/double-integrated-labels/fpd-1/?utm_source=Channel%20Grabber&utm_medium=Link&utm_campaign=FPD-1%20CG',
    ];

    public function __construct(
        Helper $helper,
        InvoiceSettingsService $invoiceSettingsService,
        TemplateService $templateService,
        ActiveUserInterface $activeUserContainer,
        InvoiceSettingsMapper $invoiceSettingsMapper,
        DataTable $datatable,
        AmazonSesService $amazonSesService,
        IntercomEventService $intercomEventService,
        IntercomCompanyService $intercomCompanyService,
        UserOrganisationUnitService $userOrganisationUnitService
    ) {
        $this->helper = $helper;
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->templateService = $templateService;
        $this->activeUserContainer = $activeUserContainer;
        $this->invoiceSettingsMapper = $invoiceSettingsMapper;
        $this->datatable = $datatable;
        $this->amazonSesService = $amazonSesService;
        $this->intercomEventService = $intercomEventService;
        $this->intercomCompanyService = $intercomCompanyService;
        $this->userOrganisationUnitService = $userOrganisationUnitService;
    }

    public function saveSettingsFromPostData($data)
    {
        /** @var Entity $invoiceSettings */
        $invoiceSettings = $this->getSettings();
        try {
            $currentAutoEmail = $invoiceSettings->getAutoEmail();
            $currentSendToFBA = $invoiceSettings->getSendToFba();
        } catch (NotFound $e) {
            $currentAutoEmail = false;
            $currentSendToFBA = false;
        }

        try {
            $data['emailSendAs'] = $this->validateEmailSendAs($data['emailSendAs']);
            $data['itemSku'] = $this->validateBoolean($data['itemSku']);
            $data['productImages'] = $this->validateBoolean($data['productImages']);
            $data['itemBarcodes'] = $this->validateBoolean($data['itemBarcodes']);
            $data['itemVariationAttributes'] = $this->validateBoolean($data['itemVariationAttributes']);
            $data['additionalShippingLabels'] = $this->validateBoolean($data['additionalShippingLabels']);
            $data['autoEmail'] = $this->handleDateTimeValue(
                $currentAutoEmail,
                $this->validateBoolean($data['autoEmail']),
                'notifyOfAutoEmailChange'
            );
            $data['sendToFba'] = $this->handleDateTimeValue(
                $currentSendToFBA,
                $this->validateBoolean($data['sendToFba'])
            );

            $data = $this->handleEmailVerification($data);

            if (! empty($data['tradingCompanies'])) {
                $data['tradingCompanies'] = $this->handleTradingCompanyEmailVerification($data['tradingCompanies'], $invoiceSettings->getTradingCompanies());
                $data['tradingCompanies'] = $this->reformatTradingCompanies($data['tradingCompanies']);
            }

            if (isset($data['confirmationAmazon']) && $this->validateBoolean($data['confirmationAmazon'])) {
                $data['useVerifiedEmailAddressForAmazonInvoices'] = true;
            }

            $settings = array_merge($invoiceSettings->toArray(), $data);
            $settings['autoEmailAllowed'] = $this->isAutoEmailAllowed($settings);
            $entity = $this->saveSettings($settings);
        } catch (NotModified $e) {
            $entity = $invoiceSettings;
        }

        return $entity;
    }

    /**
     * Reformat trading companies array to move the id key inside the trading companies data
     * and removing the named key in the main array prevents mongodb from being able to perform queries
     * on the nested data.
     *
     * @param $tradingCompanies
     * @return array
     */
    protected function reformatTradingCompanies($tradingCompanies)
    {
        $newTradingCompanies = [];

        foreach ($tradingCompanies as $key => $tradingCompany) {
            $data = ['id' => $key];
            $data = array_merge($data, $tradingCompany);
            $newTradingCompanies[] = $data;
        }

        return $newTradingCompanies;
    }

    public function getEmailVerificationStatusFromEntity(Entity $entity)
    {
        $emailVerificationStatus = [];

        if ($entity->getEmailSendAs()) {
            $emailVerificationStatus[$entity->getId()] = $this->getEmailVerificationStatusForDisplay($entity->getEmailVerificationStatus());
        }

        if (empty($tradingCompanies = $entity->getTradingCompanies())) {
            return $emailVerificationStatus;
        }

        foreach ($tradingCompanies as $tradingCompany) {
            if ($tradingCompany['emailSendAs']) {
                $emailVerificationStatus[$tradingCompany['id']] = $this->getEmailVerificationStatusForDisplay($tradingCompany['emailVerificationStatus']);
            }
        }

        return $emailVerificationStatus;
    }

    public function saveSettings($invoiceSettingsArray)
    {
        $invoiceSettingsArray['id'] = $this->getOrganisationUnitId();
        $entity = $this->invoiceSettingsMapper->fromArray(
            $invoiceSettingsArray
        );
        $entity->setStoredEtag($invoiceSettingsArray['eTag']);
        $this->invoiceSettingsService->save($entity);
        return $entity;
    }

    /**
     * @param $emailSendAs
     * @return mixed
     */
    protected function validateEmailSendAs($emailSendAs)
    {
        return filter_var($emailSendAs, FILTER_VALIDATE_EMAIL) ? $emailSendAs : null;
    }

    /**
     * @return boolean
     */
    protected function validateBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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

    protected function handleDateTimeValue($currentValue, $newValue, $notify = false)
    {
        if (((bool) $currentValue) && $newValue) {
            $newValue = $currentValue;
            // Value unchanged so don't tell intercom
        } else if ($newValue) {
            $newValue = (new DateTime())->stdFormat();
            if ($notify) {
                $this->{$notify}(true);
            }
        } else {
            $newValue = null;
            if ($notify) {
                $this->{$notify}(false);
            }
        }
        return $newValue;
    }

    /**
     * @param array $ouEmailVerificationData
     * @return array
     */
    protected function handleEmailVerification(array $ouEmailVerificationData)
    {
        if (!$ouEmailVerificationData['emailSendAs']) {
            return $this->resetVerificationStatus($ouEmailVerificationData);
        }

        $ouEmailVerificationData = $this->addCurrentVerificationStatusToData($ouEmailVerificationData);
        if ($ouEmailVerificationData['emailVerified']) {
            return $ouEmailVerificationData;
        }

        // Send verification request if there is no known status for the email account or if there is a failed status (retry)
        if (! $ouEmailVerificationData['emailVerificationStatus'] || $ouEmailVerificationData['emailVerificationStatus'] === AmazonSesService::STATUS_FAILED) {
            $this->amazonSesService->verifyEmailIdentity($ouEmailVerificationData['emailSendAs']);
            $ouEmailVerificationData = $this->addCurrentVerificationStatusToData($ouEmailVerificationData);
        }

        return $ouEmailVerificationData;
    }

    /**
     * @param array $ouEmailVerificationData
     * @return array
     */
    protected function resetVerificationStatus(array $ouEmailVerificationData)
    {
        $ouEmailVerificationData['emailVerificationStatus'] = null;
        $ouEmailVerificationData['emailVerified'] = false;
        return $ouEmailVerificationData;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function addCurrentVerificationStatusToData(array $data)
    {
        $data['emailVerificationStatus'] = $this->amazonSesService->getVerificationStatus($data['emailSendAs']);
        $data['emailVerified'] = $this->amazonSesService->isStatusVerified($data['emailVerificationStatus']);
        return $data;
    }

    /**
     * @param array $tradingCompanies
     * @param array $invoiceSettingsTradingCompanies
     * @return array
     */
    protected function handleTradingCompanyEmailVerification(array $tradingCompanies, array $invoiceSettingsTradingCompanies)
    {
        foreach ($tradingCompanies as $key => $value) {
            $tradingCompany = [
                'emailSendAs' => $this->validateEmailSendAs($tradingCompanies[$key]['emailSendAs']),
                'emailVerified' => isset($invoiceSettingsTradingCompanies[$key]['emailVerified']) ? $invoiceSettingsTradingCompanies[$key]['emailVerified'] : false,
                'emailVerificationStatus' => isset($invoiceSettingsTradingCompanies[$key]['emailVerificationStatus']) ? $invoiceSettingsTradingCompanies[$key]['emailVerificationStatus'] : null,
            ];

            if ($tradingCompany['emailSendAs']) {
                $tradingCompany = $this->handleEmailVerification($tradingCompany);
            }

            $tradingCompanies[$key] = array_merge($tradingCompanies[$key], $tradingCompany);
        }

        return $tradingCompanies;
    }

    /**
     * @param $emailVerificationStatus
     * @return array
     */
    public function getEmailVerificationStatusForDisplay($emailVerificationStatus)
    {
        $message = '';
        $message = ($emailVerificationStatus === AmazonSesService::STATUS_FAILED) ? AmazonSesService::STATUS_MESSAGE_FAILED : $message;
        $message = ($emailVerificationStatus === AmazonSesService::STATUS_PENDING) ? AmazonSesService::STATUS_MESSAGE_PENDING : $message;
        $message = ($emailVerificationStatus === AmazonSesService::STATUS_VERIFIED) ? AmazonSesService::STATUS_MESSAGE_VERIFIED : $message;

        return [
            'status' => strtolower($emailVerificationStatus),
            'class' => 'email-verify-status',
            'message' => $message
        ];
    }

    protected function isAutoEmailAllowed(array $data)
    {
        if (! $data['autoEmail']) {
            return false;
        }

        if ($data['emailVerified']) {
            return true;
        }

        foreach ($data['tradingCompanies'] as $tradingCompany) {
            if ($tradingCompany['emailVerified']) {
                return true;
            }
        }

        return false;
    }

    protected function notifyOfAutoEmailChange($enabled)
    {
        $activeUser = $this->userOrganisationUnitService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_EMAIL_INVOICE_CHANGES, $activeUser->getId(), ['email-invoice' => (boolean) $enabled]);
        $this->intercomEventService->save($event);
        $this->intercomCompanyService->save($this->userOrganisationUnitService->getRootOuByUserEntity($activeUser));
    }

    public function getSettings()
    {
        return $this->invoiceSettingsService->fetch(
            $this->getOrganisationUnitId()
        );
    }

    protected function getOrganisationUnitId()
    {
        return $this->activeUserContainer->getActiveUser()->getOrganisationUnitId();
    }

    /**
     * @deprecated use fetchTemplates()
     */
    public function getInvoices(): array
    {
        return iterator_to_array($this->fetchTemplates([TemplateType::INVOICE]));
    }

    public function fetchTemplates(array $types = null): TemplateCollection
    {
        try {
            $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = (new TemplateFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId([$organisationUnitId]);
            if (!empty($types)) {
                $filter->setType($types);
            }

            $templates = $this->templateService->fetchCollectionByFilter($filter);

        } catch (NotFound $e) {
            $templates = new TemplateCollection(Template::class, 'empty');
        }

        $defaults = $this->templateService->getDefaultTemplates($organisationUnitId);
        $templates->addAll($defaults);
        return $templates;
    }

    public function getExistingTemplatesForView()
    {
        $userTemplates = [];
        $systemTemplates[] = $this->getBlankTemplate();

        $templates = $this->fetchTemplates();

        foreach ($templates as $template) {
            $templateViewDataElement = $this->getTemplateViewData($template);

            if ($template instanceof SystemTemplate) {
                $systemTemplates[] = $templateViewDataElement;
            } else {
                $userTemplates[] = $templateViewDataElement;
            }
        }
        return ['system' => $systemTemplates, 'user' => $userTemplates];
    }

    protected function getBlankTemplate()
    {
        return [
            'name' => 'Blank',
            'key' => 'blank',
            'templateId' => '',
            'imageUrl' => Module::PUBLIC_FOLDER . static::TEMPLATE_THUMBNAIL_PATH . 'blank.png',
            'links' => [
                [
                    'name' => 'Create',
                    'key' => 'createLinkBlank',
                    'properties' => [
                        'href' => '/settings/invoice/designer',
                    ],
                ]
            ]
        ];
    }

    protected function getTemplateViewData(Template $template): array
    {
        $templateViewDataElement = $template->toArray();
        $templateViewDataElement['key'] = $template->getId();
        $templateViewDataElement['imageUrl'] = Module::PUBLIC_FOLDER.static::TEMPLATE_THUMBNAIL_PATH.$this->templateImagesMap[$template->getTypeId()];
        $templateViewDataElement['links'] = $template->getViewLinks();
        return $templateViewDataElement;
    }

    public function getTemplateOptions(array $selectedTemplateIds = []): array
    {
        $options = [];
        $templates = $this->fetchTemplates();
        foreach ($templates as $template) {
            $options[] = [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'selected' => in_array($template->getId(), $selectedTemplateIds),
                'favourite' => $template->isFavourite(),
            ];
        }
        return $options;
    }

    /**
     * @return Datatable
     */
    public function getDatatable()
    {
        return $this->datatable;
    }
}
