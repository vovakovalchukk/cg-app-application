<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\CourierAdapter\Provider\Implementation\Service;

interface ServiceAwareInterface
{
    public function setAdapterImplementationService(Service $adapterService);
}