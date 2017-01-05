<?php
namespace Settings\Invoice;

use CG\Amazon\Aws\Ses\Service as AmazonSesService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Intercom\Company\Service as IntercomCompanyService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Entity;
use CG\Settings\Invoice\Shared\Mapper as InvoiceSettingsMapper;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\Service as TemplateService;
use CG\Template\Entity as Template;
use CG\Template\SystemTemplateEntity as SystemTemplate;
use CG\User\ActiveUserInterface;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG_UI\View\DataTable;
use Settings\Module;

class Service
{
    const TEMPLATE_THUMBNAIL_PATH = 'img/InvoiceOverview/TemplateThumbnails/';
    const EVENT_SAVED_INVOICE_CHANGES = 'Saved Invoice Changes';
    const EVENT_EMAIL_INVOICE_CHANGES = 'Enable/Disable Email Invoice';

    protected $invoiceService;
    protected $templateService;
    protected $organisationUnitService;
    protected $activeUserContainer;
    protected $invoiceSettingsMapper;
    protected $datatable;
    protected $userOrganisationUnitService;
    protected $templateImagesMap = [
        'FPS-3'  => 'Form-FPS3.png',
        'FPS-15'  => 'Form-FPS15.png',
        'FPS-16'  => 'Form-FPS16.png',
        'FPD-1'  => 'Form-FPD1.png',
        Template::DEFAULT_TEMPLATE_ID => 'blank.png',
    ];
    protected $templatePurchaseLinksMap = [
        'FPS-3'  => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-3/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-3%20CG%20Link',
        'FPS-15'  => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-15/?utm_source=Channel%20Grabber&utm_medium=Link&utm_campaign=FPS-15%20CG',
        'FPS-16'  => 'https://www.formsplus.co.uk/online-shop/integrated/single-integrated-labels/fps-16/?utm_source=Channel%20Grabber&utm_medium=Link%20&utm_campaign=FPS-16%20CG%20Link',
        'FPD-1'  => 'https://www.formsplus.co.uk/online-shop/integrated/double-integrated-labels/fpd-1/?utm_source=Channel%20Grabber&utm_medium=Link&utm_campaign=FPD-1%20CG',
    ];

    public function __construct(
        InvoiceSettingsService $invoiceSettingsService,
        TemplateService $templateService,
        OrganisationUnitService $organisationUnitService,
        ActiveUserInterface $activeUserContainer,
        InvoiceSettingsMapper $invoiceSettingsMapper,
        DataTable $datatable,
        AmazonSesService $amazonSesService,
        IntercomEventService $intercomEventService,
        IntercomCompanyService $intercomCompanyService,
        UserOrganisationUnitService $userOrganisationUnitService
    ) {
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->templateService = $templateService;
        $this->organisationUnitService = $organisationUnitService;
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
        $invoiceSettings = $this->getSettings();

        try {
            $currentAutoEmail = $invoiceSettings->getAutoEmail();
        } catch (NotFound $e) {
            $currentAutoEmail = false;
        }

        try {
            $data['emailSendAs'] = $this->validateEmailSendAs($data['emailSendAs']);
            $data['autoEmail'] = $this->validateAutoEmail($data['autoEmail']);
            $data['productImages'] = $this->validateProductImages($data['productImages']);
            $data['autoEmail'] = $this->handleAutoEmailChange($currentAutoEmail, $data['autoEmail']);

            if ($data['emailSendAs']) {
                $data = $this->handleEmailVerification($data);
            }

            if (! empty($data['tradingCompanies'])) {
                $data['tradingCompanies'] = $this->handleTradingCompanyEmailVerification($data['tradingCompanies'], $invoiceSettings->getTradingCompanies());
                $data['tradingCompanies'] = $this->reformatTradingCompanies($data['tradingCompanies']);
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
     * @param $autoEmail
     * @return boolean
     */
    protected function validateAutoEmail($autoEmail)
    {
        return filter_var($autoEmail, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param $productImages
     * @return boolean
     */
    protected function validateProductImages($productImages)
    {
        return filter_var($productImages, FILTER_VALIDATE_BOOLEAN);
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
     * @param $currentAutoEmail
     * @param $autoEmail
     * @return mixed
     */
    protected function handleAutoEmailChange($currentAutoEmail, $autoEmail)
    {
        if ($currentAutoEmail && $autoEmail) {
            $autoEmail = $currentAutoEmail;
            // Value unchanged so don't tell intercom
        } else if ($autoEmail) {
            $autoEmail = (new DateTime())->stdFormat();
            $this->notifyOfAutoEmailChange(true);
        } else {
            $autoEmail = null;
            $this->notifyOfAutoEmailChange(false);
        }

        return $autoEmail;
    }

    /**
     * @param array $ouEmailVerificationData
     * @return array
     */
    protected function handleEmailVerification(array $ouEmailVerificationData)
    {
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

    protected function notifyOfSave()
    {
        $activeUser = $this->userOrganisationUnitService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_SAVED_INVOICE_CHANGES, $activeUser->getId());
        $this->intercomEventService->save($event);
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

    public function getInvoices()
    {
        $organisationUnits = [
            $this->activeUserContainer->getActiveUser()->getOrganisationUnitId()
        ];

        try {
            return $this->templateService->fetchInvoiceCollectionByOrganisationUnitWithHardCoded(
                $organisationUnits
            );
        } catch (NotFound $e) {
            return [];
        }
    }

    public function getExistingInvoicesForView()
    {
        $userInvoices = [];
        $systemInvoices[] = $this->getBlankTemplate();

        $templates = $this->getInvoices();
        foreach ($templates as $template) {
            $templateViewDataElement = $this->getTemplateViewData($template);

            if ($template instanceof SystemTemplate) {
                $systemInvoices[] = $templateViewDataElement;
            } else {
                $userInvoices[] = $templateViewDataElement;
            }
        }
        return ['system' => $systemInvoices, 'user' => $userInvoices];
    }

    private function getBlankTemplate()
    {
        return [
            'name' => 'Blank',
            'key' => 'blank',
            'invoiceId' => '',
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

    public function getTradingCompanies()
    {
        $limit = 'all';
        $page = 1;
        $ancestor = $this->activeUserContainer->getActiveUser()->getOrganisationUnitId();

        try {
            return $this->organisationUnitService->fetchFiltered(
                $limit,
                $page,
                $ancestor
            );
        } catch (NotFound $e) {
            return [];
        }
    }

    private function getTemplateViewData($template)
    {
        $templateViewDataElement['name'] = $template->getName();
        $templateViewDataElement['key'] = $template->getId();
        $templateViewDataElement['invoiceId'] = $template->getId();
        $templateViewDataElement['imageUrl'] = Module::PUBLIC_FOLDER.static::TEMPLATE_THUMBNAIL_PATH.$this->templateImagesMap[$template->getTypeId()];
        $templateViewDataElement['links'] = $template->getViewLinks();

        return $templateViewDataElement;
    }

    /**
     * @return Datatable
     */
    public function getDatatable()
    {
        return $this->datatable;
    }

    public function setDatatable(Datatable $datatable)
    {
        $this->datatable = $datatable;
        return $this;
    }
}
