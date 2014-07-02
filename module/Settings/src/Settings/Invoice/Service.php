<?php
namespace Settings\Invoice;

use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Template\Service as TemplateService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\Template\Type as TemplateType;

class Service
{
    protected $invoiceService;
    protected $templateService;
    protected $organisationUnitService;
    protected $activeUserContainer;

    public function __construct(
        InvoiceSettingsService $invoiceSettingsService,
        TemplateService $templateService,
        OrganisationUnitService $organisationUnitService,
        ActiveUserInterface $activeUserContainer
    )
    {
        $this->setInvoiceSettingsService($invoiceSettingsService)
             ->setTemplateService($templateService)
             ->setOrganisationUnitService($organisationUnitService)
             ->setActiveUserContainer($activeUserContainer);
    }

    public function saveSettings($invoiceSettingsArray)
    {
        $entity = $this->getInvoiceSettingsService()->getMapper()->fromArray(
            $invoiceSettingsArray
        );
        $this->getInvoiceSettingsService()->save($entity);
        return $entity;
    }

    public function getSettings()
    {
        return $this->getInvoiceSettingsService()->fetch(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId()
        );
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

        return $this->getTemplateService()->fetchCollectionByPagination(
            $limit,
            $page,
            $ids,
            $organisationUnits,
            $type
        );
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
    public function getInvoiceSettingsService()
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
    public function getTemplateService()
    {
        return $this->templateService;
    }

    public function setTemplateService($templateService)
    {
        $this->templateService = $templateService;
        return $this;
    }

    /**
     * @return OrganisationUnitService
     */
    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    public function setOrganisationUnitService($organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setActiveUserContainer($activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}