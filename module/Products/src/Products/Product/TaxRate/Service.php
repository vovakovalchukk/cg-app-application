<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\MemberState\Decider as MemberStateDecider;
use CG\Tax\RatesAbstract as TaxRatesAbstract;
use CG\Tax\Rates\Factory as TaxRatesFactory;

class Service
{
    /**
     * @var OrganisationUnitService $organisationUnitService
     */
    protected $organisationUnitService;
    protected $cache;
    protected $cacheDefaults;

    public function __construct(OrganisationUnitService $organisationUnitService)
    {
        $this->setOrganisationUnitService($organisationUnitService);
        $this->cache = [];
    }

    public function getTaxRatesOptionsForProduct(Product $product)
    {
        $organisationUnitId = $product->getOrganisationUnitId();

        if(isset($this->cache[$organisationUnitId])) {
            return $this->markRateOptionSelectedForProduct(
                $product, $this->cache[$organisationUnitId], $this->cacheDefaults[$organisationUnitId]
            );
        }

        $memberState = $this->fetchMemberStateForOuId($organisationUnitId);
        $rates = $this->fetchTaxRatesForMemberState($memberState);
        $defaultRate = $rates->getDefault();
        $ratesOptions = $this->buildRatesOptions($rates);
        $this->cache[$organisationUnitId] = $ratesOptions;
        $this->cacheDefaults[$organisationUnitId] = $defaultRate;

        return $this->markRateOptionSelectedForProduct($product, $ratesOptions, $defaultRate);
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
        return $taxRateFactory($memberState);
    }

    protected function buildRatesOptions(TaxRatesAbstract $rates)
    {
        $ratesOptions = [];
        foreach ($rates->getAll() as $rateId => $rate) {
            $ratesOptions[$rateId] = [
                'name' => $rate->getName(),
                'rate' => ((float) $rate->getCurrent() * (float) 100)
            ];
        }
        return $ratesOptions;
    }

    protected function markRateOptionSelectedForProduct(Product $product, $ratesOptions, $defaultRate)
    {
        $taxRateId = $product->getTaxRateId();
        if ($taxRateId == null || !isset($ratesOptions[$taxRateId])) {
            $taxRateId = $defaultRate->getId();
        }

        $ratesOptions[$taxRateId]['selected'] = true;
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

