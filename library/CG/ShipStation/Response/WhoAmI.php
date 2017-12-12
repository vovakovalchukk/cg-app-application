<?php
namespace CG\ShipStation\Response;

use CG\ShipStation\ResponseAbstract;

class WhoAmI extends ResponseAbstract
{
    protected $partnerId;

    protected function build($decodedJson): self
    {
        $this->setPartnerId($decodedJson);
        return $this;
    }

    public function getPartnerId()
    {
        return $this->partnerId;
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }
}
