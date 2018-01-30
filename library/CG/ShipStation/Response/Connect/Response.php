<?php
namespace CG\ShipStation\Response\Connect;

use CG\ShipStation\Messages\Carrier;
use CG\ShipStation\ResponseAbstract;

class Response extends ResponseAbstract
{
    /** @var  Carrier */
    protected $carrier;

    public function __construct(Carrier $carrier)
    {
        $this->carrier = $carrier;
    }

    protected static function build($decodedJson): Response
    {
        return new static((new Carrier($decodedJson->{"carrier-id"})));
    }

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }
}
