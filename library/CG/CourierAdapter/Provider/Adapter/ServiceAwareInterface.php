<?php
namespace CG\CourierAdapter\Provider\Adapter;

use CG\CourierAdapter\Provider\Adapter\Service;

interface ServiceAwareInterface
{
    public function setAdapterService(Service $adapterService);
}