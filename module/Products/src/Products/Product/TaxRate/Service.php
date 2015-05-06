<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Tax\Rates\Factory as TaxRatesFactory;

class Service
{
    protected $organisationUnitService;

    public function __construct(OrganisationUnitService $organisationUnitService)
    {
        $this->setOrganisationUnitService($organisationUnitService);
    }

    protected function fetchMemberStateForOuId($organisationUnitId)
    {
        try {
            $organisationUnit = $this->getOrganisationUnitService()->fetch($organisationUnitId);
        } catch (NotFound $e) {
            //TODO: log warning
            return [];
        }
        return $organisationUnit->getMemberState();
    }

    public function getTaxRatesArrayForOrganisationUnit()
    {
        return ['GB1' => 'Standard', 'GB2' => 'Reduced', 'GB3' => 'Zero'];
    }

    /**
     * @return OrganisationUnitService
     */
    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    /**
     * @param OrganisationUnitService $organisationUnitService
     * @return $this
     */
    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}

