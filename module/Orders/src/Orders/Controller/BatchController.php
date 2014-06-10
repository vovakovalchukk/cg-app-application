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

    public function createAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        try {
            $this->getBatchService()->createForOrders(
                (array) $this->params()->fromPost('orders', [])
            );
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function createFromFilterIdAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        try {
            $this->getBatchService()->createForFilterId(
                $this->params()->fromRoute('filterId')
            );
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function unsetAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        try {
            $this->getBatchService()->unsetForOrders(
                (array) $this->params()->fromPost('orders', [])
            );
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function unsetFromFilterIdAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        try {
            $this->getBatchService()->unsetForFilterId(
                $this->params()->fromRoute('filterId')
            );
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

    /**
     * @return BatchService
     */
    public function getBatchService()
    {
        return $this->batchService;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}