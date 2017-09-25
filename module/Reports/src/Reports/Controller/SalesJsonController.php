<?php
namespace Reports\Controller;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Reports\OrderCount\UnitService;
use Reports\Sales\Service as SalesService;

class SalesJsonController extends AbstractJsonController
{
    /** @var  SalesService */
    protected $salesService;

    public function __construct(JsonModelFactory $jsonModelFactory, SalesService $service)
    {
        parent::__construct($jsonModelFactory);
        $this->salesService = $service;
    }

    public function orderCountsAction()
    {
        $requestFilter = $this->params()->fromPost('filter', []);
        $strategy = $this->params()->fromPost('strategy', ['channel', 'total']);
        $strategyType = $this->params()->fromPost('strategyType', ['count', 'orderValue']);
        $unitType = $this->params()->fromPost('unitType', UnitService::UNIT_DAY);

        if (!is_array($strategy) || !is_array($strategyType)) {
            return $this->buildErrorResponse('An error occurred while fetching the order data.');
        }

        try {
            $orderCounts = $this->salesService->getOrderCountsData($requestFilter, $strategy, $strategyType, $unitType);
            return $this->buildSuccessResponse(['data' => $orderCounts]);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            return $this->buildErrorResponse('An error occurred while fetching the order data.');
        }
    }
}
