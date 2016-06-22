<?php
namespace Settings\Invoice;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Shared\Mapper as InvoiceSettingsMapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\Service as TemplateService;
use CG\Template\Type as TemplateType;
use CG\User\ActiveUserInterface;
use CG_UI\View\DataTable;

class Service
{
    protected $invoiceService;
    protected $templateService;
    protected $organisationUnitService;
    protected $activeUserContainer;
    protected $invoiceSettingsMapper;
    protected $datatable;

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
        $systemInvoices[] = [
            'name' => 'Blank',
            'key' => 'blank',
            'invoiceId' => '',
            'imageUrl' => '',
            'links' => [
                [
                    'name' => 'Create',
                    'key' => 'createLinkfps3',
                    'properties' => [
                        'target' => '_blank',
                        'href' => '/settings/invoice/designer',
                    ],
                ]
            ]
        ];

        $templates = $this->getInvoices();
        foreach ($templates as $template) {
            $templateViewDataElement['name'] = $template->getName();
            $templateViewDataElement['key'] = $template->getId();
            $templateViewDataElement['invoiceId'] = $template->getId();
            $templateViewDataElement['imageUrl'] = '/cg-built/settings/img/InvoiceOverview/TemplateThumbnails/Form-FPS3.png';
            $templateViewDataElement['links']  = [

            ];

            if ($template->getEditable()) {
                $userInvoices[] = $templateViewDataElement;
            } else {
                $systemInvoices[] = $templateViewDataElement;
            }
        }
//        $templates[] = [
//            'name' => 'Blankety Blank',
//            'key' => 'blank123',
//            'invoiceId' => 'default-formsPlusFPS-3_OU1',
//            'imageUrl' => '',
//            'links' => [
//                [
//                    'name' => 'Create',
//                    'key' => 'createLinkfps31',
//                    'properties' => [
//                        'target' => '_blank',
//                        'href' => '/settings/invoice/designer',
//                    ],
//                ]
//            ]
//        ];
        return ['system' => $systemInvoices, 'user' => $userInvoices];
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
