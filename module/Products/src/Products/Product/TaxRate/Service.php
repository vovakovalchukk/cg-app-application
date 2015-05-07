<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\MemberState\Decider as MemberStateDecider;
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

    public function getTaxRatesArrayForProduct(Product $product)
    {
        $organisationUnit = $this->getOrganisationUnitService()->fetch($product->getOrganisationUnitId());

        $decider = new MemberStateDecider();
        $memberState = $decider($organisationUnit);

        $taxRateFactory = new TaxRatesFactory();
        $rates = $taxRateFactory($memberState);
        $ratesArray = $rates->getAll();

        $formattedRates = [];
        foreach($ratesArray as $rateId => $rate) {
            $formattedRates[$rateId] = [
                'name' => $rate->getName(),
                'selected' => $product->getTaxRateId() === $rateId
            ];
        }

        return $formattedRates;
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

