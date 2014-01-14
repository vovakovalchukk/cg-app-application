<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Client\Batch\Storage\Api as BatchApi;
use CG\Order\Client\Storage\Api as OrderApi;
use CG\Order\Service\Filter\Entity as FilterEntity;
use CG\Order\Shared\Batch\Entity as BatchEntity;
use Zend\Di\Di;

class BatchController extends AbstractActionController
{
    protected $batchApi;
    protected $orderApi;
    protected $di;

    public function __construct(Di $di, OrderApi $orderApi, BatchApi $batchApi)
    {
        $this->setDi($di)
            ->setOrderApi($orderApi)
            ->setBatchApi($batchApi);
    }

    public function createAction()
    {
        $batch = $this->getDi()->get(BatchEntity::class, array(
            "organisationUnitId" => null //need this
        ));
        $batch = $this->getBatchApi()->save($batch);

        $orderIds = $this->params('orderIds');
        $filterEntity = $this->getDi()->get(FilterEntity::class, array(
            "limit" => null,
            "page" => null,
            "id" => $orderIds,
            "organisationUnitId" => null, //need this
            "status" => null,
            "accountId" => null,
            "channel" => null,
            "country" => null,
            "countryExclude" => null,
            "shippingMethod" => null,
            "searchTerm" => null,
            "includeArchived" => true,
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

    public function setBatchApi(BatchApi $batchApi)
    {
        $this->batchApi = $batchApi;
        return $this;
    }

    public function getBatchApi()
    {
        return $this->batchApi;
    }

    public function setOrderApi(OrderApi $orderApi)
    {
        $this->orderApi = $orderApi;
        return $this;
    }

    public function getOrderApi()
    {
        return $this->orderApi;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }
}