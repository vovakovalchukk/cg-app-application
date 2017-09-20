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
        $orderCounts = $this->salesService->getOrderCountsData($requestFilter);
        return $this->buildSuccessResponse(['data' => $orderCounts]);
    }
}
