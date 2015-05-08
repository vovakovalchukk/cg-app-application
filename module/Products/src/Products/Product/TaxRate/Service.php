<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\MemberState\Decider as MemberStateDecider;
use CG\Tax\Rates\Factory as TaxRatesFactory;

class Service
{
    /**
     * @var OrganisationUnitService $organisationUnitService
     */
    protected $organisationUnitService;
    protected $cache;

    public function __construct(OrganisationUnitService $organisationUnitService)
    {
        $this->setOrganisationUnitService($organisationUnitService);
        $this->cache = [];
    }

    public function getTaxRatesOptionsForProduct(Product $product)
    {
        $organisationUnitId = $product->getOrganisationUnitId();

        if(isset($this->cache[$organisationUnitId])) {
            return $this->markRateOptionSelectedForProduct($product, $this->cache[$organisationUnitId]);
        }

        $memberState = $this->fetchMemberStateForOuId($organisationUnitId);
        $rates = $this->fetchTaxRatesForMemberState($memberState);
        $ratesOptions = $this->buildRatesOptions($rates);

        if(!isset($this->cache[$organisationUnitId])) {
            $this->cache[$organisationUnitId] = $ratesOptions;
        }

        return $this->markRateOptionSelectedForProduct($product, $ratesOptions);
    }

    protected function fetchMemberStateForOuId($organisationUnitId)
    {
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $decider = new MemberStateDecider();
        return $decider($organisationUnit);
    }

    protected function fetchTaxRatesForMemberState($memberState)
    {
        $taxRateFactory = new TaxRatesFactory();
        return $taxRateFactory($memberState)->getAll();
    }

    protected function buildRatesOptions($rates)
    {
        $ratesOptions = [];
        foreach($rates as $rateId => $rate) {
            $ratesOptions[$rateId] = [
                'name' => $rate->getName(),
                'rate' => (int) ($rate->getCurrent() * 100)
            ];
        }
        return $ratesOptions;
    }

    protected function markRateOptionSelectedForProduct(Product $product, $ratesOptions)
    {
        if($product->getTaxRateId() == null || !isset($ratesOptions[$product->getTaxRateId()])) {
            return $ratesOptions;
        }

        $ratesOptions[$product->getTaxRateId()]['selected'] = true;
        return $ratesOptions;
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

