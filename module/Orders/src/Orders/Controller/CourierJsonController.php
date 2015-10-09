<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Courier\Label\CancelService as LabelCancelService;
use Orders\Courier\Label\CreateService as LabelCreateService;
use Orders\Courier\Label\ReadyService as LabelReadyService;
use Orders\Courier\Manifest\Service as ManifestService;
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
    const ROUTE_LABEL_CANCEL = 'Cancel';
    const ROUTE_LABEL_CANCEL_URI = '/cancel';
    const ROUTE_LABEL_READY_CHECK = 'Ready Check';
    const ROUTE_LABEL_READY_CHECK_URI = '/readyCheck';
    const ROUTE_MANIFEST_ACCOUNTS = 'Accounts';
    const ROUTE_MANIFEST_ACCOUNTS_URI = '/accounts';
    const ROUTE_MANIFEST_DETAILS = 'Details';
    const ROUTE_MANIFEST_DETAILS_URI = '/details';

    protected $jsonModelFactory;
    /** @var Service */
    protected $service;
    /** @var LabelCreateService */
    protected $labelCreateService;
    /** @var LabelCancelService */
    protected $labelCancelService;
    /** @var LabelReadyService */
    protected $labelReadyService;
    /** @var ManifestService */
    protected $manifestService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service,
        LabelCreateService $labelCreateService,
        LabelCancelService $labelCancelService,
        LabelReadyService $labelReadyService,
        ManifestService $manifestService
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setLabelCreateService($labelCreateService)
            ->setLabelCancelService($labelCancelService)
            ->setLabelReadyService($labelReadyService)
            ->setManifestService($manifestService);
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
        $orderIds = $this->params()->fromPost('order', []);
        $ordersData = $this->params()->fromPost('orderData', []);
        $ordersParcelsData = $this->params()->fromPost('parcelData', []);
        $this->sanitiseInputArray($ordersData);
        $this->sanitiseInputArray($ordersParcelsData);
        $labelReadyStatuses = $this->labelCreateService->createForOrdersData($orderIds, $ordersData, $ordersParcelsData, $accountId);
        $readyCount = 0;
        $notReadyCount = 0;
        foreach ($labelReadyStatuses as $labelReadyStatus) {
            if ($labelReadyStatus) {
                $readyCount++;
            } else {
                $notReadyCount++;
            }
        }
        return $this->jsonModelFactory->newInstance([
            'readyStatuses' => $labelReadyStatuses,
            'readyCount' => $readyCount,
            'notReadyCount' => $notReadyCount,
        ]);
    }

    public function cancelAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderIds = $this->params()->fromPost('order');
        $this->labelCancelService->cancelForOrders($orderIds, $accountId);
        return $this->jsonModelFactory->newInstance([]);
    }

    public function readyCheckAction()
    {
        $orderIds = $this->params()->fromPost('order');
        $readyOrderIds = $this->labelReadyService->checkForOrders($orderIds);
        return $this->jsonModelFactory->newInstance([
            'readyOrders' => $readyOrderIds,
        ]);
    }

    public function manifestAccountsAction()
    {
        $accountOptions = $this->manifestService->getShippingAccountOptions();
        return $this->jsonModelFactory->newInstance(['accounts' => $accountOptions]);
    }

    public function manifestDetailsAction()
    {
        $accountId = $this->params()->fromPost('account');
        $details = $this->manifestService->getDetailsForShippingAccount($accountId);
        return $this->jsonModelFactory->newInstance($details);
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

    protected function setLabelCreateService(LabelCreateService $labelCreateService)
    {
        $this->labelCreateService = $labelCreateService;
        return $this;
    }

    protected function setLabelCancelService(LabelCancelService $labelCancelService)
    {
        $this->labelCancelService = $labelCancelService;
        return $this;
    }

    protected function setLabelReadyService(LabelReadyService $labelReadyService)
    {
        $this->labelReadyService = $labelReadyService;
        return $this;
    }

    protected function setManifestService(ManifestService $manifestService)
    {
        $this->manifestService = $manifestService;
        return $this;
    }
}
