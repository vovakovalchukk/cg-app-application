<?php
namespace Orders\Order\Filter;

use CG\Constant\Log\User\Permission\OrganisationUnit;
use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\User\ActiveUserInterface;
use CG\OrganisationUnit\Service as OrganisationUnitService;

class Shipping implements SelectOptionsInterface
{
    protected $service;
    protected $activeUserContainer;
    protected $organisationUnitService;

    public function __construct(
        ShippingConversionService $service,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService
    )
    {
        $this->setService($service)
             ->setActiveUserContainer($activeUserContainer)
             ->setOrganisationUnitService($organisationUnitService);
    }

    public function getSelectOptions()
    {
        $options = [];
        $organisationUnit = $this->getOrganisationUnitService()
                                 ->fetch($this->getActiveUserContainer()
                                              ->getActiveUserRootOrganisationUnitId()
            );
        $aliases = $this->getService()->fetchAliases($organisationUnit);
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

    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}