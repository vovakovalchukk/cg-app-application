<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Batch\Service as BatchService;

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

    public function createAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $ids = $this->params()->fromPost('orders');
        if (!is_array($ids) || empty($ids)) {
            return $response->setVariable('error', 'No Orders provided');
        }
        $this->getBatchService()->create($ids);
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