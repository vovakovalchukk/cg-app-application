<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
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
    const ROUTE_MANIFEST = 'Manifest';
    const ROUTE_MANIFEST_URI = '/manifest';
    const ROUTE_MANIFEST_ACCOUNTS = 'Accounts';
    const ROUTE_MANIFEST_ACCOUNTS_URI = '/accounts';
    const ROUTE_MANIFEST_DETAILS = 'Details';
    const ROUTE_MANIFEST_DETAILS_URI = '/details';
    const ROUTE_MANIFEST_HISTORIC = 'Historic';
    const ROUTE_MANIFEST_HISTORIC_URI = '/historic';

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
        try {
            $labelReadyStatuses = $this->labelCreateService->createForOrdersData($orderIds, $ordersData, $ordersParcelsData, $accountId);
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to create label(s), please check the details you\'ve entered and try again', $e->getCode(), $e
            );
        } catch (ValidationMessagesException $e) {
            $message = $this->validationExceptionToMessage($e);
            throw new \RuntimeException($message);
        }
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

    protected function validationExceptionToMessage(ValidationMessagesException $e)
    {
        $message = 'Failed to create label(s), for the following reasons: <div><ul>';
        $orderFieldErrors = [];
        foreach ($e->getErrors() as $field => $errorMessage) {
            $fieldParts = explode(':', $field);
            if (count($fieldParts) > 1) {
                $orderId = trim($fieldParts[0]);
                $orderNumber = preg_replace('/^[0-9]+-/', '', $orderId);
                $fieldName = trim($fieldParts[1]);
            } else {
                $orderNumber = '';
                $fieldName = $field;
            }
            if (!isset($orderFieldErrors[$orderNumber])) {
                $orderFieldErrors[$orderNumber] = [];
            }
            $orderFieldErrors[$orderNumber][$fieldName] = $errorMessage;
        }

        foreach ($orderFieldErrors as $orderNumber => $errorFields) {
            $message .= '<li>' . $orderNumber . ':<ul>';
            foreach ($errorFields as $fieldName => $errorMessage) {
                $message .= '<li>' . ($fieldName ? $fieldName . ': ' : '') . $errorMessage . '</li>';
            }
            $message .= '</ul></li>';
        }
        $message .= '</ul></div>';
        return $message;
    }

    public function cancelAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderIds = $this->params()->fromPost('order');
        try {
            $this->labelCancelService->cancelForOrders($orderIds, $accountId);
            return $this->jsonModelFactory->newInstance([]);
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to cancel shipping order(s), please try again', $e->getCode(), $e
            );
        }
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
        $data = ['accounts' => $accountOptions];
        foreach ($accountOptions as $accountOption) {
            if (!isset($accountOption['selected']) || $accountOption['selected'] == false) {
                continue;
            }
            $data['selectedAccount'] = $accountOption['value'];
            $data = array_merge($data, $this->getManifestDetailsForShippingAccount($accountOption['value']));
        }
        return $this->jsonModelFactory->newInstance($data);
    }

    public function manifestDetailsAction()
    {
        $accountId = $this->params()->fromPost('account');
        $details = $this->getManifestDetailsForShippingAccount($accountId);
        return $this->jsonModelFactory->newInstance($details);
    }

    protected function getManifestDetailsForShippingAccount($accountId)
    {
        $details = $this->manifestService->getDetailsForShippingAccount($accountId);
        $data = ['details' => $details];
        $data = array_merge($data, $this->getHistoricManifestPeriodOptionsForShippingAccount($accountId));
        return $data;
    }

    public function historicManifestsAction()
    {
        $accountId = $this->params()->fromPost('account');
        $year = $this->params()->fromPost('year');
        $month = $this->params()->fromPost('month');
        $data = $this->getHistoricManifestPeriodOptionsForShippingAccount($accountId, $year, $month);
        return $this->jsonModelFactory->newInstance($data);
    }

    protected function getHistoricManifestPeriodOptionsForShippingAccount($shippingAccountId, $year = null, $month = null)
    {
        $data = ['historic' => []];
        if (!$year) {
            $data['historic']['yearOptions'] = $this->manifestService->getHistoricManifestYearsForShippingAccount($shippingAccountId);
            foreach ($data['historic']['yearOptions'] as $yearOption) {
                if (!isset($yearOption['selected']) || $yearOption['selected'] == false) {
                    continue;
                }
                $year = $yearOption['value'];
                break;
            }
        }
        if ($year && !$month) {

            $data['historic']['monthOptions'] = $this->manifestService->getHistoricManifestMonthsForShippingAccount($shippingAccountId, $year);
            foreach ($data['historic']['monthOptions'] as $monthOption) {
                if (!isset($monthOption['selected']) || $monthOption['selected'] == false) {
                    continue;
                }
                $month = $monthOption['value'];
                break;
            }
        }
        if ($year && $month) {
            $data['historic']['dateOptions'] = $this->manifestService->getHistoricManifestDatesForShippingAccount($shippingAccountId, $year, $month);
        }
        return $data;
    }

    public function createManifestAction()
    {
        $accountId = $this->params()->fromPost('account');
        try {
            $accountManifest = $this->manifestService->generateManifestForShippingAccount($accountId);
            return $this->jsonModelFactory->newInstance(['id' => $accountManifest->getId()]);
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to generate manifest, please check the details you\'ve entered and try again', $e->getCode(), $e
            );
        }
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
