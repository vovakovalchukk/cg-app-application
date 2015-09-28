<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Courier\Label\Service as LabelService;
use Orders\Courier\Service;
use Zend\Mvc\Controller\AbstractActionController;

class CourierJsonController extends AbstractActionController
{
    const ROUTE_REVIEW_LIST = 'Review List';
    const ROUTE_REVIEW_LIST_URI = '/ajax';
    const ROUTE_SPECIFICS_LIST = 'Specifics List';
    const ROUTE_SPECIFICS_LIST_URI = '/ajax';
    const ROUTE_LABEL_CREATE = 'Create';
    const ROUTE_LABEL_CREATE_URI = '/create';

    protected $jsonModelFactory;
    /** @var Service */
    protected $service;
    /** @var LabelService */
    protected $labelService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service,
        LabelService $labelService
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setLabelService($labelService);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function reviewListAction()
    {
        $data = $this->getDefaultJsonData();
        $orderIds = $this->params()->fromPost('order', []);
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = count($orderIds);
        if (!empty($orderIds)) {
            $data['Records'] = $this->service->getReviewListData($orderIds);
        }

        return $this->jsonModelFactory->newInstance($data);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function specificsListAction()
    {
        $data = $this->getDefaultJsonData();
        $orderIds = $this->params()->fromPost('order', []);
        $courierId = $this->params()->fromRoute('account');
        $ordersData = $this->params()->fromPost('orderData', []);
        $ordersParcelsData = $this->params()->fromPost('parcelData', []);
        $this->sanitiseInputArray($ordersData);
        $this->sanitiseInputArray($ordersParcelsData);

        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = count($orderIds);
        if (!empty($orderIds)) {
            $data['Records'] = $this->service->getSpecificsListData($orderIds, $courierId, $ordersData, $ordersParcelsData);
        }

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

    protected function sanitiseInputArray(array &$inputArray)
    {
        foreach ($inputArray as &$array) {
            foreach ($array as $key => $value) {
                if (is_numeric($value)) {
                    $array[$key] = (float)$value;
                }
            }
        }
    }

    public function createLabelAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderId = $this->params()->fromPost('order');
        $ordersData = $this->params()->fromPost('orderData', []);
        $ordersParcelsData = $this->params()->fromPost('parcelData', []);
        $this->sanitiseInputArray($ordersData);
        $this->sanitiseInputArray($ordersParcelsData);
        $orderData = $ordersData[$orderId];
        $parcelsData = $ordersParcelsData[$orderId];
        $this->labelService->createForOrderData($orderId, $orderData, $parcelsData, $accountId);

        return $this->jsonModelFactory->newInstance([]);
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function setLabelService(LabelService $labelService)
    {
        $this->labelService = $labelService;
        return $this;
    }
}
