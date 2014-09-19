<?php
namespace Orders\Controller;

use Orders\Order\Service as OrderService;
use Orders\Order\Batch\Service as BatchService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use CG_UI\View\Prototyper\JsonModelFactory;

class StoredBatchesController extends AbstractActionController
{
    const ROUTE_REMOVE = "batch remove";

    protected $batchService;
    protected $orderService;
    protected $jsonModelFactory;

    public function __construct(
        BatchService $batchService,
        OrderService $orderService,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->setBatchService($batchService)
            ->setOrderService($orderService)
            ->setJsonModelFactory($jsonModelFactory);
    }

    public function removeBatchAction()
    {
        $jsonModel = $this->getJsonModelFactory()->newInstance(['removed' => false]);

        $batchId = $this->params()->fromPost('id');

        try {
            $this->getBatchService()->setInactive($batchId);
        } catch (\Exception $e) {
            return $jsonModel->setVariable('error', 'Batch could not removed. Please try again.');
        }

        return $jsonModel->setVariable('removed', true);
    }

    /**
     * @return BatchService
     */
    protected function getBatchService()
    {
        return $this->batchService;
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getOrderService()
    {
        return $this->orderService;
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }
}
 