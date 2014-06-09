<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Batch\Service as BatchService;
use CG\Stdlib\Exception\Runtime\RequiredKeyMissing;
use CG\Order\Service\Filter;

class BatchController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $batchService;

    public function __construct(JsonModelFactory $jsonModelFactory, BatchService $batchService)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setBatchService($batchService);
    }

    public function indexAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $batches = $this->getBatchService()->getBatches();
        $response->setVariables($batches);
        return $response;
    }

    protected function getOrderFilter(array $ids)
    {
        return $this->getBatchService()->getDi()->newInstance(
            Filter::class,
            [
                'page' => 1,
                'limit' => 'all',
                'orderIds' => $ids,
            ]
        );
    }

    public function createAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $ids = $this->params()->fromPost('orders', []);
        try {
            $batchService = $this->getBatchService();
            $orders = $batchService->getOrderClient()->fetchCollectionByFilter(
                $this->getOrderFilter((array) $ids)
            );
            $batchService->create($orders);
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function createFromFilterIdAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $filterId = $this->params()->fromRoute('filterId');
        try {
            $batchService = $this->getBatchService();
            $orders = $batchService->getOrderClient()->fetchCollectionByFilterId(
                $filterId,
                'all',
                1,
                null,
                null
            );
            $batchService->create($orders);
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function unsetAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $ids = $this->params()->fromPost('orders');
        try {
            $batchService = $this->getBatchService();
            $orders = $batchService->getOrderClient()->fetchCollectionByFilter(
                $this->getOrderFilter((array) $ids)
            );
            $this->getBatchService()->unsetBatch($orders);
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        } catch (\Exception $e) {
            echo $e->getPrevious()->getResponse()->getMessage();
        }
        return $response;
    }

    public function unsetFromFilterIdAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $filterId = $this->params()->fromRoute('filterId');
        try {
            $batchService = $this->getBatchService();
            $orders = $batchService->getOrderClient()->fetchCollectionByFilterId(
                $filterId,
                'all',
                1,
                null,
                null
            );
            $this->getBatchService()->unsetBatch($orders);
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function deleteAction($batchId)
    {
        $this->getBatchService()->delete($batchId);
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    public function getBatchService()
    {
        return $this->batchService;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}