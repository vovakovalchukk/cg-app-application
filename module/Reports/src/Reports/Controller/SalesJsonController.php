<?php
namespace Reports\Controller;

use Application\Controller\AbstractJsonController;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Reports\Order\Service as ReportsOrderService;

class SalesJsonController extends AbstractJsonController
{
    /** @var  ReportsOrderService */
    protected $orderService;

    public function __construct(JsonModelFactory $jsonModelFactory, ReportsOrderService $service)
    {
        parent::__construct($jsonModelFactory);
        $this->orderService = $service;
    }

    public function orderCountsAction()
    {
        $orderFilter = $this->params()->fromPost('filter', []);
        $dimension = $this->params()->fromPost('dimension', ReportsOrderService::DIMENSION_CHANNEL);
        $metrics = $this->params()->fromPost('metrics', []);
        $limit = (int) $this->params()->fromPost('limit', ReportsOrderService::DEFAULT_POINTS_LIMIT);

        // Make sure that we will take all orders into account
        $orderFilter['archived'] = [true, false];

        try {
            return $this->buildSuccessResponse([
                'data' => $this->orderService->getOrderCountsData($dimension, $metrics, $orderFilter, $limit)
            ]);
        } catch (NotFound $e) {
            return $this->buildErrorResponse('There is no data available for the selected filters');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            return $this->buildErrorResponse('An error occurred while fetching the order data.');
        }
    }
}
