<?php
namespace Orders\Order\Batch;

use CG\User\ActiveUserInterface;
use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use Orders\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    protected $organisationUnitService;
    protected $batchClient;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const ACTIVE = 1;

    public function __construct(OrganisationUnitService $organisationUnitService, BatchInterface $batchClient)
    {
        $this->setOrganisationUnitService($organisationUnitService)
            ->setBatchClient($batchClient);
    }

    public function getBatches()
    {
        $organisationUnitIds = $this->getOrganisationUnitService()->getAncestorOrganisationUnitIds();
        try {
            $batchCollection = $this->getBatchClient()->fetchCollectionByPagination(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $organisationUnitIds, static::ACTIVE);
        } catch (NotFound $exception) {
            $batchCollection = new \SplObjectStorage();
        }
        return $batchCollection;
    }

    public function setBatchClient(BatchInterface $batchClient)
    {
        $this->batchClient = $batchClient;
        return $this;
    }

    public function getBatchClient()
    {
        return $this->batchClient;
    }

    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    public function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }
}