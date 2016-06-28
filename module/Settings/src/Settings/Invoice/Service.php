<?php
namespace Settings\Invoice;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Mapper as InvoiceSettingsMapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\Service as TemplateService;
use CG\Template\Entity as Template;
use CG\Template\SystemTemplateEntity as SystemTemplate;
use CG\User\ActiveUserInterface;
use CG_UI\View\DataTable;
use Settings\Module;

class Service
{
    protected $invoiceService;
    protected $templateService;
    protected $organisationUnitService;
    protected $activeUserContainer;
    protected $invoiceSettingsMapper;
    protected $datatable;

    const TEMPLATE_THUMBNAIL_PATH = 'img/InvoiceOverview/TemplateThumbnails/';
    protected $templateImagesMap = [
        'FPS-3'  => 'Form-FPS3.png',
        'FPS-15'  => 'Form-FPS15.png',
        'FPS-16'  => 'Form-FPS16.png',
        'FPD-1'  => 'Form-FPD1.png',
        Template::DEFAULT_TEMPLATE_ID => 'Default.png',
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
        DataTable $datatable
    ) {
        $this->setInvoiceSettingsService($invoiceSettingsService)
             ->setTemplateService($templateService)
             ->setOrganisationUnitService($organisationUnitService)
             ->setActiveUserContainer($activeUserContainer)
             ->setInvoiceSettingsMapper($invoiceSettingsMapper)
             ->setDatatable($datatable);
    }

    public function saveSettings($invoiceSettingsArray)
    {
        $invoiceSettingsArray['id'] = $this->getOrganisationUnitId();
        $entity = $this->getInvoiceSettingsMapper()->fromArray(
            $invoiceSettingsArray
        );
        $entity->setStoredEtag($invoiceSettingsArray['eTag']);
        $this->getInvoiceSettingsService()->save($entity);
        return $entity;
    }

    public function getSettings()
    {
        return $this->getInvoiceSettingsService()->fetch(
            $this->getOrganisationUnitId()
        );
    }

    protected function getOrganisationUnitId()
    {
        return $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId();
    }

    public function getInvoices()
    {
        $organisationUnits = [
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId()
        ];

        try {
            return $this->getTemplateService()->fetchInvoiceCollectionByOrganisationUnitWithHardCoded(
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

    private function getTemplateViewData($template)
    {
        $templateViewDataElement['name'] = $template->getName();
        $templateViewDataElement['key'] = $template->getId();
        $templateViewDataElement['invoiceId'] = $template->getId();
        $templateViewDataElement['imageUrl'] = Module::PUBLIC_FOLDER.static::TEMPLATE_THUMBNAIL_PATH.$this->templateImagesMap[$template->getTypeId()];
        $templateViewDataElement['links'] = $template->getViewLinks();

        return $templateViewDataElement;
    }

    private function getBlankTemplate()
    {
        return [
            'name' => 'Blank',
            'key' => 'blank',
            'invoiceId' => '',
            'imageUrl' => '',
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
        $ancestor = $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId();

        try {
            return $this->getOrganisationUnitService()->fetchFiltered(
                $limit,
                $page,
                $ancestor
            );
        } catch (NotFound $e) {
            return [];
        }
    }

    /**
     * @return InvoiceSettingsService
     */
    protected function getInvoiceSettingsService()
    {
        return $this->invoiceSettingsService;
    }

    public function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }

    /**
     * @return TemplateService
     */
    protected function getTemplateService()
    {
        return $this->templateService;
    }

    public function setTemplateService(TemplateService $templateService)
    {
        $this->templateService = $templateService;
        return $this;
    }

    /**
     * @return OrganisationUnitService
     */
    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return InvoiceSettingsMapper
     */
    protected function getInvoiceSettingsMapper()
    {
        return $this->invoiceSettingsMapper;
    }

    public function setInvoiceSettingsMapper(InvoiceSettingsMapper $invoiceSettingsMapper)
    {
        $this->invoiceSettingsMapper = $invoiceSettingsMapper;
        return $this;
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
