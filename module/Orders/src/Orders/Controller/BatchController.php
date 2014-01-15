<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG\User\ActiveUserInterface;
use CG\Order\Shared\StorageInterface as OrderInterface;
use CG\Order\Shared\Batch\StorageInterface as BatchInterface;
use CG\OrganisationUnit\StorageInterface as OrganisationUnitInterface;
use CG\Order\Service\Filter\Entity as FilterEntity;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use Zend\Di\Di;

class BatchController extends AbstractActionController
{
    protected $activeUserContainer;
    protected $orderClient;
    protected $organisationUnitClient;
    protected $batchClient;

    const DEFAULT_LIMIT = "ALL";
    const DEFAULT_PAGE = 0;
    const DEFAULT_INCLUDE_ARCHIVED = 1;

    public function __construct(Di $di, ActiveUserInterface $activeUserContainer, OrderInterface $orderClient,
                                OrganisationUnitInterface $organisationUnitClient, BatchInterface $batchClient)
    {
        $this->setActiveUserContainer($activeUserContainer)
            ->setOrderClient($orderClient)
            ->setOrganisationUnitClient($organisationUnitClient)
            ->setBatchClient($batchClient);
    }

    public function createAction()
    {
        $userEntity = $this->getService()->getActiveUser();
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => $userEntity->getOrganisationUnitId()
        ));
        $batch = $this->getBatchApi()->save($batch);

        try {
            $organisationUnits = $this->getService()->getOrganisationUnitClient()->fetchFiltered(static::DEFAULT_LIMIT,
                static::DEFAULT_PAGE, $userEntity->getOrganisationUnitId());
        } catch (NotFound $exception) {
            $organisationUnits = new \SplObjectStorage();
        }
        $organisationUnitIds = array($userEntity->getOrganisationUnitId());
        foreach ($organisationUnits as $organisationUnit) {
            $organisationUnitIds[] = $organisationUnit->getId();
        }
        $orderIds = $this->params('orderIds');
        $filterEntity = $this->getDi()->get(FilterEntity::class, array(
            "limit" => static::DEFAULT_LIMIT,
            "page" => static::DEFAULT_PAGE,
            "id" => $orderIds,
            "organisationUnitId" => $organisationUnitIds,
            "status" => null,
            "accountId" => null,
            "channel" => null,
            "country" => null,
            "countryExclude" => null,
            "shippingMethod" => null,
            "searchTerm" => null,
            "includeArchived" => static::DEFAULT_INCLUDE_ARCHIVED,
            "multiLineSameOrder" => null,
            "multiSameItem" => null,
            "timeFrom" => null,
            "timeTo" => null,
            "orderBy" => null,
            "orderDirection" => null,
            "batch" => null
        ));
        $orders = $this->getOrderApi()->fetchCollectionByFilter($filterEntity);
        $rollback = array();
        try {
            foreach ($orders as $index => $order) {
                $rollback[$index] = $order->getBatch();
                $order->setBatch($batch->getId());
                $this->getOrderApi()->save($order);
            }
        }
        catch (\Exception $e) {
            try {
                foreach ($rollback as $index => $batchId) {
                    $orders[$index]->setBatch($batchId);
                    $this->getOrderApi()->save($orders[$index]);
                }
                $this->delete($batchId->getId());
            } catch (\Exception $e) {
                //Shits Really Hit The Fan
            }
            throw $e;
        }
    }

    public function deleteAction($batchId)
    {
        $this->delete($batchId);
    }

    protected function delete($batchId)
    {
        $entity = $this->getBatchApi()->fetch($batchId);
        $this->getBatchApi()->remove($entity);
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

    public function setBatchClient(BatchInterface $batchClient)
    {
        $this->batchClient = $batchClient;
        return $this;
    }

    public function getBatchClient()
    {
        return $this->batchClient;
    }

    public function setOrderClient(OrderInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
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