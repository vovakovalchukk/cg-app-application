<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Dataplug\Client as DataplugClient;
use CG\Dataplug\Request\RetrieveOrders as DataplugGetOrdersRequest;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use Orders\Courier\Label\MissingException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LABEL_MAX_ATTEMPTS = 10;
    const LABEL_ATTEMPT_INTERVAL_SEC = 1;

    const LOG_CODE = 'OrderCourierLabelService';
    const LOG_CREATE = 'Create label request for Order %s, shipping Account %d';
    const LOG_CREATE_SEND = 'Sending create request to Dataplug for Order %s, shipping Account %d';
    const LOG_CREATE_MISSING_REF = 'Dataplug response from create request missing Order->OrderNumber for Order %s, shipping Account %d';
    const LOG_CREATE_REF = 'Successfully created order with Dataplug and got order number %s for Order %s, shipping Account %d';
    const LOG_CREATE_DONE = 'Completed create label request for Order %s, shipping Account %d';
    const LOG_GET_LABEL_ATTEMPT = 'Attempt %d to get label data for order number %s, Order %s, shipping Account %d';
    const LOG_GET_LABEL_RETRY = 'No label data found on this attempt, will retry for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_LABEL_FAILED = 'Max attempts (%d) to get label data reached for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET = 'Getting order details from Dataplug for reference %s, shipping Account %d';
    const LOG_GET_MISSING_ITEMS = 'Dataplug response from retrieve request missing parcel data for Order %s';
    const LOG_GET_MISSING_LABEL = 'Dataplug response from retrieve request missing label data for Order %s';
    const LOG_SAVE = 'Saving OrderLabel for Order %s';
    const LOG_PDF_MERGE = 'Merging multiple label PDFs into one';
    const LOG_PDF_MERGE_WRITE_FAIL = 'Error writing PDF data to file';
    const LOG_PDF_MERGE_FAIL = 'Error merging PDF data';

    /** @var Mapper */
    protected $mapper;
    /** @var UserOUService */
    protected $userOUService;
    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var DataplugClient */
    protected $dataplugClient;
    /** @var OrderLabelMapper */
    protected $orderLabelMapper;
    /** @var OrderLabelService */
    protected $orderLabelService;

    public function __construct(
        Mapper $mapper,
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        DataplugClient $dataplugClient,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService
    ) {
        $this->setMapper($mapper)
            ->setUserOUService($userOuService)
            ->setOrderService($orderService)
            ->setAccountService($accountService)
            ->setDataplugClient($dataplugClient)
            ->setOrderLabelMapper($orderLabelMapper)
            ->setOrderLabelService($orderLabelService);
    }

    public function createForOrderData($orderId, array $orderData, array $parcelData, $accountId)
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParam('order', $orderId)->addGlobalLogEventParam('account', $accountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CREATE, [$orderId, $accountId], static::LOG_CODE);
        $account = $this->accountService->fetch($accountId);
        $order = $this->orderService->fetch($orderId);
        $request = $this->mapper->orderAndDataToDataplugCreateRequest($order, $orderData, $parcelData, $rootOu);
        $this->logDebug(static::LOG_CREATE_SEND, [$orderId, $accountId], static::LOG_CODE);
        $response = $this->dataplugClient->sendRequest($request, $account);
        if (!isset($response->Order, $response->Order->OrderNumber)) {
            throw new \RuntimeException(vsprintf(static::LOG_CREATE_MISSING_REF, [$orderId, $accountId]));
        }
        $orderNumber = (string)$response->Order->OrderNumber;
        $this->logDebug(static::LOG_CREATE_REF, [$orderNumber, $orderId, $accountId], static::LOG_CODE);

        $labelData = $this->getOrderLabelPdfData($order, $account, $orderNumber);
        $this->saveLabelDataForAnOrder($order, $labelData);
        $this->logDebug(static::LOG_CREATE_DONE, [$orderId, $accountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('order')->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
    }

    protected function getOrderLabelPdfData(Order $order, Account $account, $orderNumber, $attempt = 1)
    {
        try {
            $this->logDebug(static::LOG_GET_LABEL_ATTEMPT, [$attempt, $orderNumber, $order->getId(), $account->getId()]);
            $response = $this->getDataplugOrderByOrderNumber($orderNumber, $account);
            return $this->getOrderLabelPdfDataFromRetrieveResponse($order, $response);
        } catch (MissingException $e) {
            if ($attempt >= static::LABEL_MAX_ATTEMPTS) {
                $this->logError(static::LOG_GET_LABEL_FAILED, [static::LABEL_MAX_ATTEMPTS, $orderNumber, $order->getId(), $account->getId()]);
                throw $e;
            }
            $this->logDebug(static::LOG_GET_LABEL_RETRY, [$orderNumber, $order->getId(), $account->getId()]);
            sleep(static::LABEL_ATTEMPT_INTERVAL_SEC);
            return $this->getOrderLabelPdfData($order, $account, $orderNumber, ++$attempt);
        }
    }

    protected function getDataplugOrderByOrderNumber($orderNumber, $account)
    {
        $this->logDebug(static::LOG_GET, [$orderNumber, $account->getId()], static::LOG_CODE);
        $request = new DataplugGetOrdersRequest();
        $request->setSearchType(DataplugGetOrdersRequest::SEARCH_TYPE_ORDER_NUMBER)
            ->setIdentifier($orderNumber)
            ->setMarkPrinted(false);
        return $this->dataplugClient->sendRequest($request, $account);
    }

    protected function getOrderLabelPdfDataFromRetrieveResponse(Order $order, \SimpleXMLElement $response)
    {
        if (isset($response->Order, $response->Order->Label, $response->Order->Label->Content)) {
            return (string)$response->Order->Label->Content;
        }
        if (!isset($response->Order, $response->Order->ShipmentDetails, $response->Order->ShipmentDetails->Items, $response->Order->ShipmentDetails->Items->Item)) {
            throw new MissingException(vsprintf(static::LOG_GET_MISSING_ITEMS, [$order->getId()]));
        }
        $labelPdfData = [];
        foreach ($response->Order->ShipmentDetails->Items->Item as $parcelDetails)
        {
            if (!isset($parcelDetails->Label->Content)) {
                throw new MissingException(vsprintf(static::LOG_GET_MISSING_LABEL, [$order->getId()]));
            }
            $labelPdfData[] = (string)$parcelDetails->Label->Content;
        }
        return $this->mergePdfData($labelPdfData);
    }

    protected function saveLabelDataForAnOrder(Order $order, $labelData)
    {
        $this->logDebug(static::LOG_SAVE, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::NOT_PRINTED,
            'created' => $date->stdFormat(),
            'label' => $labelData
        ];
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);
        return $this->orderLabelService->save($orderLabel);
    }

    protected function mergePdfData(array $pdfsData)
    {
        if (count($pdfsData) == 1) {
            return $pdfsData[0];
        }
        $this->logDebug(static::LOG_PDF_MERGE, [], static::LOG_CODE);
        $fileNames = [];
        foreach ($pdfsData as $pdfData) {
            $fileName = '/tmp/label-data-'.microtime(true).'.pdf';
            $result = file_put_contents($fileName, $pdfData);
            if (!$result) {
                throw new \RuntimeException(static::LOG_PDF_MERGE_WRITE_FAIL);
            }
            $fileNames[] = $fileName;
        }

        $outputFileName = '/tmp/label-data-merged-'.microtime(true).'.pdf';
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $outputFileName . ' ' . implode(' ', $fileNames);
        $result = shell_exec($cmd);
        if ($result === null) {
            throw new \RuntimeException(static::LOG_PDF_MERGE_FAIL);
        }
        $mergedPdfData = file_get_contents($outputFileName);
        unlink($outputFileName);
        foreach ($fileNames as $fileName) {
            unlink($fileName);
        }
        return $mergedPdfData;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }
    
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setDataplugClient(DataplugClient $dataplugClient)
    {
        $this->dataplugClient = $dataplugClient;
        return $this;
    }

    protected function setOrderLabelMapper(OrderLabelMapper $orderLabelMapper)
    {
        $this->orderLabelMapper = $orderLabelMapper;
        return $this;
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }
}