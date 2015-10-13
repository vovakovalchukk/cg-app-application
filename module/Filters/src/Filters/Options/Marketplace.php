<?php
namespace Filters\Options;

use CG\Listing\Unimported\Marketplace\Entity;
use CG\Listing\Unimported\Marketplace\Filter;
use CG\Listing\Unimported\Marketplace\Service;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Filters\SelectOptionsInterface;

class Marketplace implements SelectOptionsInterface
{
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var Service $service */
    protected $service;

    public function __construct(ActiveUserInterface $activeUserContainer, Service $service)
    {
        $this->setActiveUserContainer($activeUserContainer)->setService($service);
    }

    /**
     * @return array key => value pairs to be added to select filter options
     */
    public function getSelectOptions()
    {
        $marketplaces = [];
        try {
            $filter = new Filter($this->getActiveUser()->getOuList());
            
            /** @var Entity $marketplace */
            foreach ($this->service->fetchCollectionByFilter($filter) as $marketplace) {
                $marketplaces[$marketplace->getMarketplace()] = $marketplace->getMarketplace();
            }
        } catch (NotFound $exception) {
            // No marketplacces have been assigned to OU
        }
        return $marketplaces;
    }

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
} 
