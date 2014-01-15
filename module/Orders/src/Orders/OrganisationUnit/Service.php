<?php
namespace Orders\OrganisationUnit;

use CG\User\ActiveUserInterface;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    protected $activeUserContainer;
    protected $organisationUnitClient;
    protected $batchClient;

    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = "all";

    public function __construct(ActiveUserInterface $activeUserContainer, OrganisationUnitInterface $organisationUnitClient)
    {
        $this->setActiveUserContainer($activeUserContainer)
            ->setOrganisationUnitClient($organisationUnitClient);
    }

    public function getAncestorOrganisationUnitIds()
    {
        $userEntity = $this->getActiveUser();
        try {
            $organisationUnits = $this->getOrganisationUnitClient()->fetchFiltered(static::DEFAULT_PAGE, static::DEFAULT_LIMIT,
                $userEntity->getOrganisationUnitId());
        } catch (NotFound $exception) {
            $organisationUnits = new \SplObjectStorage();
        }

        $organisationUnitIds = array($userEntity->getOrganisationUnitId());
        foreach ($organisationUnits as $organisationUnit) {
            $organisationUnitIds[] = $organisationUnit->getId();
        }
        return $organisationUnitIds;
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setOrganisationUnitClient(OrganisationUnitInterface $organisationUnitClient)
    {
        $this->organisationUnitClient = $organisationUnitClient;
        return $this;
    }

    public function getOrganisationUnitClient()
    {
        return $this->organisationUnitClient;
    }
}