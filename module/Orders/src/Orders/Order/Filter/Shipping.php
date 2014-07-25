<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\User\ActiveUserInterface;

class Shipping implements SelectOptionsInterface
{
    protected $service;
    protected $activeUserContainer;

    public function __construct(ShippingConversionService $service, ActiveUserInterface $activeUserContainer)
    {
        $this->setService($service)
             ->setActiveUserContainer($activeUserContainer);
    }

    public function getSelectOptions()
    {
        $options = [];
        $aliases = $this->getService()->fetchAliases($this->getActiveUserContainer()->getOrganisationUnitId());
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

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}