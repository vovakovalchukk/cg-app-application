<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
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
        $orderIds = $this->params()->fromPost('orderIds');
        $this->getBatchService()->create($orderIds);
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