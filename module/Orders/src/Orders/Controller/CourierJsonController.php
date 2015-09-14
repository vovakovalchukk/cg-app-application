<?php
namespace Orders\Controller;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class CourierJsonController extends AbstractActionController
{
    const ROUTE_REVIEW_LIST = 'Review List';
    const ROUTE_REVIEW_LIST_URI = '/ajax';

    protected $jsonModelFactory;
    protected $orderService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setOrderService($orderService);
    }

    public function reviewListAction()
    {
        $data = $this->getDefaultJsonData();
        $orderIds = $this->params('order', []);

        $filter = new OrderFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setId($orderIds);
        $orders = $this->orderService->fetchCollectionByFilter($filter);
        // TODO: populate $data['Records'] from $orders

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function getDefaultJsonData()
    {
        return [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }
}
