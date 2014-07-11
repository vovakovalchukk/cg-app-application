<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;

class Shipping implements SelectOptionsInterface
{
    protected $service;

    public function __construct(ShippingConversionService $service)
    {
        $this->setService($service);
    }

    public function getSelectOptions()
    {
        $options = [];
        $aliases = $this->getService()->fetchAliases();
        foreach ($aliases as $alias) {
            $options[$alias->getId()] = $alias->getName();
        }
        return $options;
    }

    protected function setService(ShippingConversionService $service)
    {
        $this->service = $service;
        return $this;
    }

    protected function getService()
    {
        return $this->service;
    }
}