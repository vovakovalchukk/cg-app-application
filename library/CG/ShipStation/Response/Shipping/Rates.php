<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Messages\Rate;
use CG\ShipStation\ResponseAbstract;

class Rates extends ResponseAbstract
{
    /** @var Rate[] */
    protected $rates;

    public function __construct(Rate ...$rates)
    {
        $this->rates = $rates;
    }

    protected static function build($decodedJson)
    {
        $rates = [];
        foreach ($decodedJson->rate_response->rates as $rateJson) {
            $rates[] = Rate::build($rateJson);
        }
        return new static(...$rates);
    }

    /**
     * @return array Rate[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }
}