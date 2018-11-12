<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Entity as OrganisationUnit;
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

    public function getTaxRatesOptionsForProduct(Product $product, OrganisationUnit $rootOu)
    {
        $organisationUnitId = $product->getOrganisationUnitId();

        if(isset($this->cache[$organisationUnitId])) {
            return $this->markRateOptionSelectedForProduct(
                $product, $this->cache[$organisationUnitId], $this->cacheDefaults[$organisationUnitId]
            );
        }

        $ratesOptions = $this->getTaxRatesOptionsForOu($rootOu);
        $defaultRates = $this->getDefaultTaxRatesForOu($rootOu);

        foreach ($product->getTaxRateIds() as $memberState => $taxRateId) {
            $rates = $this->fetchTaxRatesForMemberState($memberState);
            $defaultRates[$memberState] = $rates->getDefault();
            $ratesOptions[$memberState] = $this->buildRatesOptions($rates);
        }
        $this->cache[$organisationUnitId] = $ratesOptions;
        $this->cacheDefaults[$organisationUnitId] = $defaultRates;

        return $this->markRateOptionSelectedForProduct($product, $ratesOptions, $defaultRates);
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
                'rate' => bcmul($rate->getCurrent(), 100, 2)
            ];
        }
        return $ratesOptions;
    }

    protected function markRateOptionSelectedForProduct(Product $product, $ratesOptions, $defaultRate)
    {
        $taxRateIds = $product->getTaxRateIds();

        foreach ($ratesOptions as $memberState => $taxRate) {
            if (isset($taxRateIds[$memberState])) {
                $taxRateId = $taxRateIds[$memberState];
            } else {
                $taxRateId = $defaultRate[$memberState]->getId();
            }
            $ratesOptions[$memberState][$taxRateId]['selected'] = true;
        }

        return $ratesOptions;
    }

    protected function getTaxRatesOptionsForOu(OrganisationUnit $ou): array
    {
        $ratesOptions = [];
        foreach ($ou->getMemberState() as $memberStateOfOu) {
            $rates = $this->fetchTaxRatesForMemberState($memberStateOfOu);
            $ratesOptions[$memberStateOfOu] = $this->buildRatesOptions($rates);
        }
        return $ratesOptions;
    }

    protected function getDefaultTaxRatesForOu(OrganisationUnit $ou): array
    {
        $defaultRates = [];
        foreach ($ou->getMemberState() as $memberStateOfOu) {
            $rates = $this->fetchTaxRatesForMemberState($memberStateOfOu);
            $defaultRates[$memberStateOfOu] = $rates->getDefault();
        }
        return $defaultRates;
    }

    protected function markRateOptionSelectedForOu(OrganisationUnit $ou, $ratesOptions, $defaultRates): array
    {
        foreach ($ratesOptions as $memberState => $taxRate) {
            $taxRateId = $defaultRates[$memberState]->getId();
            $ratesOptions[$memberState][$taxRateId]['selected'] = true;
        }
        return $ratesOptions;
    }

    public function getTaxRatesOptionsForOuIdWithDefaultsSelected(int $ouId): array
    {
        $ou = $this->organisationUnitService->fetch($ouId);
        return $this->getTaxRatesOptionsForOuWithDefaultsSelected($ou);
    }

    public function  getTaxRatesOptionsForOuWithDefaultsSelected(OrganisationUnit $ou): array
    {
        $ratesOptions = $this->getTaxRatesOptionsForOu($ou);
        $defaultRates = $this->getDefaultTaxRatesForOu($ou);
        return $this->markRateOptionSelectedForOu($ou, $ratesOptions, $defaultRates);
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

