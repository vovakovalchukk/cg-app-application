<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Batch\Service as BatchService;

class BatchController extends AbstractActionController
{
    protected $batchService;

    public function __construct(BatchService $batchService)
    {
        $this->setBatchService($batchService);
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
}