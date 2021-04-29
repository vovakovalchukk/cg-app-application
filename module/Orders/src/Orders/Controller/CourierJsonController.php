<?php
namespace Orders\Controller;

use CG\Billing\Shipping\Ledger\Exception\InsufficientBalanceException;
use CG\CourierAdapter\Exception\UserError;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG_UI\View\Helper\Mustache as MustacheViewHelper;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Courier\Label\CancelService as LabelCancelService;
use Orders\Courier\Label\DispatchService as LabelDispatchService;
use Orders\Courier\Label\CreateService as LabelCreateService;
use Orders\Courier\Label\NoOrdersSelectedException;
use Orders\Courier\Label\ReadyService as LabelReadyService;
use Orders\Courier\Label\RatesService;
use Orders\Courier\Manifest\Service as ManifestService;
use Orders\Courier\ReviewAjax as ReviewAjaxService;
use Orders\Courier\ShippingAccountsService;
use Orders\Courier\SpecificsAjax as SpecificsAjaxService;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Account\Client\Service as AccountService;
use CG\Account\Client\Entity as Account;

class CourierJsonController extends AbstractActionController
{
    const ROUTE_SERVICES = 'Services';
    const ROUTE_SERVICES_FOR_ORDERS = 'Services For Orders';
    const ROUTE_CHECK_SERVICES_FOR_ORDERS = 'Check Services For Orders';
    const ROUTE_REVIEW_LIST = 'Review List';
    const ROUTE_REVIEW_LIST_URI = '/ajax';
    const ROUTE_SPECIFICS_LIST = 'Specifics List';
    const ROUTE_SPECIFICS_LIST_URI = '/ajax';
    const ROUTE_SPECIFICS_OPTIONS = 'Options';
    const ROUTE_SPECIFICS_OPTION_DATA = 'Option Data';
    const ROUTE_SPECIFICS_FETCH_SHIPPING_LEDGER_BALANCE = 'Fetch Shipping Ledger Balance';
    const ROUTE_LABEL_CREATE = 'Create';
    const ROUTE_LABEL_CREATE_URI = '/create';
    const ROUTE_LABEL_CANCEL = 'Cancel';
    const ROUTE_LABEL_CANCEL_URI = '/cancel';
    const ROUTE_LABEL_DISPATCH = 'Dispatch';
    const ROUTE_LABEL_DISPATCH_URI = '/dispatch';
    const ROUTE_LABEL_READY_CHECK = 'Ready Check';
    const ROUTE_LABEL_READY_CHECK_URI = '/readyCheck';
    const ROUTE_LABEL_FETCH_RATES = 'Fetch Rates';
    const ROUTE_MANIFEST = 'Manifest';
    const ROUTE_MANIFEST_URI = '/manifest';
    const ROUTE_MANIFEST_ACCOUNTS = 'Accounts';
    const ROUTE_MANIFEST_ACCOUNTS_URI = '/accounts';
    const ROUTE_MANIFEST_DETAILS = 'Details';
    const ROUTE_MANIFEST_DETAILS_URI = '/details';
    const ROUTE_MANIFEST_HISTORIC = 'Historic';
    const ROUTE_MANIFEST_HISTORIC_URI = '/historic';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ReviewAjaxService */
    protected $reviewAjaxService;
    /** @var SpecificsAjaxService */
    protected $specificsAjaxService;
    /** @var LabelCreateService */
    protected $labelCreateService;
    /** @var LabelCancelService */
    protected $labelCancelService;
    /** @var LabelDispatchService */
    protected $labelDispatchService;
    /** @var LabelReadyService */
    protected $labelReadyService;
    /** @var ManifestService */
    protected $manifestService;
    /** @var RatesService */
    protected $ratesService;
    /** @var AccountService */
    protected $accountService;

    protected $errorMessageMap = [
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        ReviewAjaxService $reviewAjaxService,
        SpecificsAjaxService $specificsAjaxService,
        LabelCreateService $labelCreateService,
        LabelCancelService $labelCancelService,
        LabelReadyService $labelReadyService,
        LabelDispatchService $labelDispatchService,
        ManifestService $manifestService,
        RatesService $ratesService,
        AccountService $accountService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->viewModelFactory = $viewModelFactory;
        $this->reviewAjaxService = $reviewAjaxService;
        $this->specificsAjaxService = $specificsAjaxService;
        $this->labelCreateService = $labelCreateService;
        $this->labelCancelService = $labelCancelService;
        $this->labelDispatchService = $labelDispatchService;
        $this->labelReadyService = $labelReadyService;
        $this->manifestService = $manifestService;
        $this->ratesService = $ratesService;
        $this->accountService = $accountService;
    }

    public function servicesOptionsAction()
    {
        $orderId = $this->params()->fromPost('order');
        $shippingAccountId = $this->params()->fromPost('account');
        $orderData = $this->params()->fromPost('orderData', []);

        $servicesOptions = $this->reviewAjaxService->getServicesOptionsForOrderAndAccount($orderId, $shippingAccountId, $orderData);
        return $this->jsonModelFactory->newInstance(['serviceOptions' => $servicesOptions]);
    }

    public function servicesOptionsForOrdersAction()
    {
        $orderIds = $this->params()->fromPost('order');
        $shippingAccountId = $this->params()->fromPost('account');
        $orderData = $this->params()->fromPost('orderData', []);

        $servicesOptions = $this->reviewAjaxService->getServicesOptionsForOrdersAndAccount($orderIds, $shippingAccountId, $orderData);
        return $this->jsonModelFactory->newInstance(['serviceOptions' => $servicesOptions]);
    }

    public function checkServicesOptionsForOrdersAction()
    {
        $orderIds = $this->params()->fromPost('order');
        $shippingAccountId = $this->params()->fromPost('account');

        $servicesOptions = $this->reviewAjaxService->checkServicesOptionsForOrdersAndAccount($orderIds, $shippingAccountId);
        return $this->jsonModelFactory->newInstance(['serviceOptions' => $servicesOptions]);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function reviewListAction()
    {
        $data = $this->getDefaultJsonData();
        $orderIds = $this->params()->fromPost('order', []);
        if (!empty($orderIds)) {
            $data['Records'] = $this->reviewAjaxService->getReviewListData($orderIds);
        }
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = $this->countOrderRecords($data['Records']);

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
        $rawOrdersData = $this->sanitiseInputArray($this->params()->fromPost('orderData', []));
        $rawOrdersParcelsData = $this->sanitiseInputArray($this->params()->fromPost('parcelData', []));

        $this->decodeItemParcelAssignment($rawOrdersParcelsData)
            ->assignParcelNumbers($rawOrdersParcelsData);

        $ordersData = OrderDataCollection::fromArray($rawOrdersData);
        $ordersParcelsData = OrderParcelsDataCollection::fromArray($rawOrdersParcelsData);

        if (!empty($orderIds)) {
            $data['Records'] = $this->specificsAjaxService->getSpecificsListData($orderIds, $courierId, $ordersData, $ordersParcelsData);
            $data['metadata'] = $this->specificsAjaxService->getSpecificsMetaDataFromRecords($data['Records']);
        }
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = $this->countOrderRecords($data['Records']);

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

    protected function sanitiseInputArray(array $inputArray): array
    {
        foreach ($inputArray as &$array) {
            foreach ($array as $key => $value) {
                if (is_numeric($value)) {
                    $array[$key] = (float)$value;
                }
            }
        }
        return $inputArray;
    }

    protected function countOrderRecords($records)
    {
        $resultCount = 0;
        foreach ($records as $row) {
            if (isset($row['orderRow']) && $row['orderRow']) {
                $resultCount++;
            }
        }
        return $resultCount;
    }

    public function createLabelAction()
    {
        $accountId = $this->params()->fromPost('account');
        $shippingAccount = $this->accountService->fetch($accountId);
        $orderIds = $this->params()->fromPost('order', []);
        $rawOrdersData = $this->sanitiseInputArray($this->params()->fromPost('orderData', []));
        $rawOrdersParcelsData = $this->sanitiseInputArray($this->params()->fromPost('parcelData', []));
        $rawOrdersItemsData = $this->params()->fromPost('itemData', []);

        $this->decodeItemParcelAssignment($rawOrdersParcelsData)
            ->assignParcelNumbers($rawOrdersParcelsData);

        $ordersData = OrderDataCollection::fromArray($rawOrdersData);
        $ordersParcelsData = OrderParcelsDataCollection::fromArray($rawOrdersParcelsData);
        $orderItemsData = OrderItemsDataCollection::fromArray($rawOrdersItemsData);

        try {
            $labelReadyStatuses = $this->labelCreateService->createForOrdersData(
                $orderIds, $ordersData, $ordersParcelsData, $orderItemsData, $shippingAccount
            );
            $jsonView = $this->handleFullOrPartialCreationSuccess($labelReadyStatuses);
            $jsonView->setVariable('Records', $this->specificsAjaxService->getSpecificsListData($orderIds, $accountId, $ordersData, $ordersParcelsData));
            return $jsonView;
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to create label(s), please check the details you\'ve entered and try again', $e->getCode(), $e
            );
        } catch (ValidationMessagesException $e) {
            return $this->handleLabelCreationFailure($e);
        } catch (UserError $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        } catch (InsufficientBalanceException $e) {
            return $this->handleLabelCreationInsufficientBalance($e, $ordersData);
        } catch (NoOrdersSelectedException $e) {
            return $this->redirectWhenNoOrdersSelected();
        }
    }

    protected function assignParcelNumbers(&$ordersParcelsData)
    {
        foreach ($ordersParcelsData as &$parcelsData) {
            $parcelCount = 1;
            foreach ($parcelsData as &$parcelData) {
                $parcelData['number'] = $parcelCount;
                $parcelCount++;
            }
        }
        return $this;
    }

    protected function decodeItemParcelAssignment(&$ordersParcelsData)
    {
        foreach ($ordersParcelsData as &$parcelsData) {
            foreach ($parcelsData as &$parcelData) {
                if (!isset($parcelData['itemParcelAssignment'])) {
                    continue;
                }
                $parcelData['itemParcelAssignment'] = json_decode($parcelData['itemParcelAssignment'], true);
            }
        }
        return $this;
    }

    protected function handleLabelCreationFailure(
        ValidationMessagesException $e
    ) {
        $orderFieldErrors = $this->validationExceptionToPerOrderErrorArray($e);
        $message = $this->getValidationFailureMessage($orderFieldErrors);
        return $this->jsonModelFactory->newInstance([
            'readyStatuses' => [],
            'readyCount' => 0,
            'notReadyCount' => 0,
            'errorCount' => count($orderFieldErrors),
            'partialErrorMessage' => $message,
        ]);
    }

    protected function handleFullOrPartialCreationSuccess(
        array $labelReadyStatuses
    ) {
        $readyCount = 0;
        $notReadyCount = 0;
        $errorCount = 0;
        $orderFieldErrors = [];
        foreach ($labelReadyStatuses as $labelReadyStatus) {
            if ($labelReadyStatus instanceof ValidationMessagesException) {
                $errorCount++;
                $orderFieldErrors = array_merge($orderFieldErrors, $this->validationExceptionToPerOrderErrorArray($labelReadyStatus));
            } elseif ($labelReadyStatus === true) {
                $readyCount++;
            } else {
                $notReadyCount++;
            }
        }
        $partialErrorMessage = $this->getPartialErrorMessage($orderFieldErrors);
        return $this->jsonModelFactory->newInstance([
            'readyStatuses' => $labelReadyStatuses,
            'readyCount' => $readyCount,
            'notReadyCount' => $notReadyCount,
            'errorCount' => $errorCount,
            'partialErrorMessage' => $partialErrorMessage,
            'balance' => $this->specificsAjaxService->getShippingLedgerForActiveUser()->getBalance()
        ]);
    }

    protected function validationExceptionToPerOrderErrorArray(ValidationMessagesException $e)
    {
        $orderFieldErrors = [];
        foreach ($e->getErrors() as $field => $errorMessage) {
            $fieldParts = explode(':', $field);
            if (count($fieldParts) > 1) {
                $orderId = trim($fieldParts[0]);
                $fieldName = trim($fieldParts[1]);
            } else {
                $orderId = '';
                $fieldName = $field;
            }
            $fieldName = ($fieldName ? $fieldName. ': ' : '');
            if (!isset($orderFieldErrors[$orderId])) {
                $orderFieldErrors[$orderId] = [];
            }
            $orderFieldErrors[$orderId][$fieldName] = $errorMessage;
        }
        return $orderFieldErrors;
    }

    protected function convertOrderErrorFieldsForMustache(array $orderErrorFields)
    {
        $output = [];
        foreach ($orderErrorFields as $orderId => $errorFields) {
            $orderNumber = preg_replace('/^[0-9]+-/', '', $orderId);
            $orderOutput = [
                'orderNumber' => $orderNumber,
                'errorFields' => []
            ];
            foreach ($errorFields as $fieldName => $errorMessage) {
                $orderOutput['errorFields'] = [
                    'fieldName' => $fieldName,
                    'errorMessage' => $this->mapErrorMessagesForOutput($errorMessage)
                ];
            }
            $output[] = $orderOutput;
        }
        return $output;
    }

    protected function mapErrorMessagesForOutput($errorMessage)
    {
        foreach ($this->errorMessageMap as $regex => $output) {
            if (preg_match($regex, $errorMessage)) {
                return $output;
            }
        }
        return $errorMessage;
    }

    protected function getValidationFailureMessage(array $orderFieldErrors)
    {
        $template = 'courier/messages/label-creation/failure.mustache';
        return $this->getErrorMessageFromOrderFieldErrors($orderFieldErrors, $template);
    }

    protected function getPartialErrorMessage(array $orderFieldErrors)
    {
        $template = 'courier/messages/label-creation/partialError.mustache';
        return $this->getErrorMessageFromOrderFieldErrors($orderFieldErrors, $template);
    }

    protected function getErrorMessageFromOrderFieldErrors(array $orderFieldErrors, $template)
    {
        if (empty($orderFieldErrors)) {
            return '';
        }
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('orderErrorList', $this->getErrorMessagePartialFromOrderFieldErrors($orderFieldErrors))
            ->setTemplate($template);
        $viewRender = $this->getServiceLocator()->get(MustacheViewHelper::class);
        $message = $viewRender($view);
        return $message;
    }

    protected function getErrorMessagePartialFromOrderFieldErrors(array $orderFieldErrors)
    {
        $formattedOrderErrorFields = $this->convertOrderErrorFieldsForMustache($orderFieldErrors);
        $orderErrorsView = $this->viewModelFactory->newInstance();
        $orderErrorsView->setVariable('orderErrors', array_values($formattedOrderErrorFields))
            ->setTemplate('courier/messages/label-creation/orderErrorList.mustache');
        $viewRender = $this->getServiceLocator()->get(MustacheViewHelper::class);
        return $viewRender($orderErrorsView);
    }

    protected function handleLabelCreationInsufficientBalance(InsufficientBalanceException $e, OrderDataCollection $ordersData)
    {
        $viewData = [
            'readyStatuses' => [],
            'readyCount' => 0,
            'notReadyCount' => 0,
            'errorCount' => count($ordersData),
            'topupRequired' => true,
            'partialErrorMessage' => 'You have insufficient funds to create these labels.<br />Please top up your balance or enable automatic top up.',
        ];

        return $this->jsonModelFactory->newInstance($viewData);
    }

    public function optionsAction()
    {
        $courierId = $this->params()->fromRoute('account');
        $orderId = $this->params()->fromPost('order');
        $service = $this->params()->fromPost('service');

        $options = $this->specificsAjaxService->getCarrierOptionsForService($orderId, $courierId, $service);
        return $this->jsonModelFactory->newInstance(['requiredFields' => $options]);
    }

    public function optionDataAction()
    {
        $courierId = $this->params()->fromRoute('account');
        $orderId = $this->params()->fromPost('order');
        $option = $this->params()->fromPost('option');
        $service = $this->params()->fromPost('service');

        $optionData = $this->specificsAjaxService->getDataForCarrierOption($option, $orderId, $courierId, $service);
        return $this->jsonModelFactory->newInstance([$option => $optionData]);
    }

    public function cancelAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderIds = $this->params()->fromPost('order');
        try {
            $this->labelCancelService->cancelForOrders($orderIds, $accountId);
            $jsonView = $this->jsonModelFactory->newInstance([]);
            $jsonView->setVariable('Records', $this->specificsAjaxService->getSpecificsListData($orderIds, $accountId));
            return $jsonView;
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to cancel shipping order(s), please try again', $e->getCode(), $e
            );
        } catch (NoOrdersSelectedException $e) {
            return $this->redirectWhenNoOrdersSelected();
        }
    }

    public function dispatchAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderIds = $this->params()->fromPost('order');
        try {
            $this->labelDispatchService->dispatchOrders($orderIds, $accountId);
            $jsonView = $this->jsonModelFactory->newInstance([]);
            $jsonView->setVariable('Records', $this->specificsAjaxService->getSpecificsListData($orderIds, $accountId));
            return $jsonView;
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to dispatch shipping order(s), please try again', $e->getCode(), $e
            );
        } catch (NoOrdersSelectedException $e) {
            return $this->redirectWhenNoOrdersSelected();
        }
    }

    public function readyCheckAction()
    {
        try {
            $orderIds = $this->params()->fromPost('order');
            $readyOrderIds = $this->labelReadyService->checkForOrders($orderIds);
            return $this->jsonModelFactory->newInstance([
                'readyOrders' => $readyOrderIds,
            ]);
        } catch (NoOrdersSelectedException $e) {
            return $this->redirectWhenNoOrdersSelected();
        }
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
            $returnData = ['success' => true];
            if ($accountManifest) {
                $returnData['id'] = $accountManifest->getId();
            }
            return $this->jsonModelFactory->newInstance($returnData);
        } catch (StorageException $e) {
            throw new \RuntimeException(
                'Failed to generate manifest, please check the details you\'ve entered and try again', $e->getCode(), $e
            );
        }
    }

    public function fetchRatesAction()
    {
        $accountId = $this->params()->fromPost('account');
        $orderIds = $this->params()->fromPost('order', []);
        $rawOrdersData = $this->sanitiseInputArray($this->params()->fromPost('orderData', []));
        $rawOrdersParcelsData = $this->sanitiseInputArray($this->params()->fromPost('parcelData', []));
        $rawOrdersItemsData = $this->params()->fromPost('itemData', []);

        $this->decodeItemParcelAssignment($rawOrdersParcelsData)
            ->assignParcelNumbers($rawOrdersParcelsData);

        $ordersData = OrderDataCollection::fromArray($rawOrdersData);
        $ordersParcelsData = OrderParcelsDataCollection::fromArray($rawOrdersParcelsData);
        $ordersItemsData = OrderItemsDataCollection::fromArray($rawOrdersItemsData);

        try {
            $rates = $this->ratesService->fetchRates(
                $orderIds, $ordersData, $ordersParcelsData, $ordersItemsData, $accountId
            );
            return $this->jsonModelFactory->newInstance(['rates' => $rates->toArray()]);

        } catch (ValidationMessagesException $e) {
            $errors = $this->convertValidationMessagesExceptionToArray($e);
            return $this->jsonModelFactory->newInstance(['rates' => [], 'errors' => $errors]);
        } catch (NoOrdersSelectedException $e) {
            return $this->redirectWhenNoOrdersSelected();
        }
    }

    protected function convertValidationMessagesExceptionToArray(ValidationMessagesException $e): array
    {
        return array_map(function($orderExternalId, $error)
        {
            return $orderExternalId . ': ' . $error;
        }, array_keys($e->getErrors()), array_values($e->getErrors()));
    }

    public function fetchShippingLedgerBalanceAction()
    {
        $shippingLedger = $this->specificsAjaxService->getShippingLedgerForActiveUser();
        return $this->jsonModelFactory->newInstance(['shippingLedger' => $shippingLedger->toArray()]);
    }

    protected function redirectWhenNoOrdersSelected()
    {
        return $this->redirect()->toRoute('Orders');
    }
}
