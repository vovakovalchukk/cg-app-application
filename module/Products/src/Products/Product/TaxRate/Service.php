<?php
namespace Products\Product\TaxRate;

use CG\Product\Entity as Product;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\MemberState\Decider as MemberStateDecider;
use CG\Tax\Rates\Factory as TaxRatesFactory;

class Service
{
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

        $organisationUnit = $this->getOrganisationUnitService()->fetch($organisationUnitId);

        $decider = new MemberStateDecider();
        $memberState = $decider($organisationUnit);

        $taxRateFactory = new TaxRatesFactory();
        $rates = $taxRateFactory($memberState)->getAll();

        $ratesOptions = [];
        foreach($rates as $rateId => $rate) {
            $ratesOptions[$rateId] = [
                'name' => $rate->getName(),
                'rate' => (int) ($rate->getCurrent() * 100)
            ];
        }

        if(!isset($this->cache[$organisationUnitId])) {
            $this->cache[$organisationUnitId] = $ratesOptions;
        }

        return $this->markRateOptionSelectedForProduct($product, $ratesOptions);
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

