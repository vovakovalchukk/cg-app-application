<?php
namespace Reports\Controller;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
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
        $strategyType = $this->params()->fromPost('strategyType', 'count');

        try {
            $orderCounts = $this->salesService->getOrderCountsData($strategy, $strategyType, $requestFilter);
            return $this->buildSuccessResponse(['data' => $orderCounts]);
        } catch (\Exception $e) {
            return $this->buildErrorResponse('An error occurred while fetching the order data.');
        }
    }
}
