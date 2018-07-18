<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Messages\Rate;
use CG\ShipStation\ResponseAbstract;

class Rates extends ResponseAbstract
{
    /** @var Rate[] */
    protected $rates = [];
    /** @var Rate[] */
    protected $invalidRates = [];

    protected static function build($decodedJson)
    {
        $rates = new static();
        foreach ($decodedJson->rate_response->rates as $rateJson) {
            $rates->addRate(Rate::build($rateJson));
        }
        if (!isset($decodedJson->rate_response->invalid_rates)) {
            return $rates;
        }
        foreach ($decodedJson->rate_response->invalid_rates as $invalidRateJson) {
            $rates->addInvalidRate(Rate::build($invalidRateJson));
        }
        return $rates;
    }

    public function addRate(Rate $rate): Rates
    {
        $this->rates[] = $rate;
        return $this;
    }

    public function addInvalidRate(Rate $rate): Rates
    {
        $this->invalidRates[] = $rate;
        return $this;
    }

    /**
     * @return array Rate[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     * @return array Rate[]
     */
    public function getInvalidRates(): array
    {
        return $this->invalidRates;
    }
}