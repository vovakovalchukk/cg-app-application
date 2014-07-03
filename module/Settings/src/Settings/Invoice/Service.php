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
        $limit = 'all';
        $page = 1;
        $ids = [];
        $organisationUnits = [
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId()
        ];
        $type = [
            TemplateType::INVOICE
        ];

        try {
            return $this->getTemplateService()->fetchCollectionByPagination(
                $limit,
                $page,
                $ids,
                $organisationUnits,
                $type
            );
        } catch (NotFound $e) {
            return [];
        }
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
