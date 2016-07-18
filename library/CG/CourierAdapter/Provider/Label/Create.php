<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Mapper;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\FetchingInterface as ShipmentFetchingInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\Http\StatusCode as HttpStatusCode;
use CG\Order\Client\Gearman\WorkerFunction\OrderLabelPdfToPng as OrderLabelPdfToPngWorkerFunction;
use CG\Order\Client\Gearman\Workload\OrderLabelPdfToPng as OrderLabelPdfToPngWorkload;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\PngToPdfConverter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\Entity as User;
use GearmanClient;

class Create implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'CourierAdapterProviderLabelCreate';
    const LOG_START = 'Create labels request received for OU %d, Shipping Account %d (carrier %s), orders %s';
    const LOG_END = 'Finished creating labels for OU %d, Shipping Account %d (carrier %s), orders %s';
    const LOG_SHIPMENTS = 'Creating shipments from Orders for OU %d, Shipping Account %d (carrier %s)';
    const LOG_BOOK = 'Booking shipments for OU %d, Shipping Account %d (carrier %s)';
    const LOG_BOOK_INVALID = 'Booking shipment for Order %s failed with a UserError exception, message "%s"';
    const LOG_BOOK_ERROR = 'Booking shipment for Order %s resulted in an OperationFailed exception with message "%s"';
    const LOG_TRACKING = 'Saving tracking number for Order %s, OU %d of %s';
    const LOG_NO_TRACKING = 'No tracking numbers for Order %s, OU %d';
    const LOG_FETCH_LABELS = 'Attempting to fetch shipment label(s) for Order %s';

    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var Mapper */
    protected $mapper;
    /** @var OrderTrackingMapper */
    protected $orderTrackingMapper;
    /** @var OrderTrackingService */
    protected $orderTrackingService;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var PngToPdfConverter */
    protected $pngToPdfConverter;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        Mapper $mapper,
        OrderTrackingMapper $orderTrackingMapper,
        OrderTrackingService $orderTrackingService,
        OrderLabelService $orderLabelService,
        GearmanClient $gearmanClient,
        PngToPdfConverter $pngToPdfConverter
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setMapper($mapper)
            ->setOrderTrackingMapper($orderTrackingMapper)
            ->setOrderTrackingService($orderTrackingService)
            ->setOrderLabelService($orderLabelService)
            ->setGearmanClient($gearmanClient)
            ->setPngToPdfConverter($pngToPdfConverter);
    }

    /**
     * @return array ['{orderId}' => bool || \CG\Stdlib\Exception\Runtime\ValidationMessagesException]
     */
    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        User $user
    ) {
        $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'rootOu' => $rootOu->getId(), 'account' => $shippingAccount->getId(), 'channel' => $shippingAccount->getChannel()]);
        $this->logInfo(static::LOG_START, [$rootOu->getId(), $shippingAccount->getId(), $shippingAccount->getChannel(), implode(', ', $orders->getIds())], [static::LOG_CODE, 'Start']);

        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($shippingAccount);
        $this->logDebug(static::LOG_SHIPMENTS, [$rootOu->getId(), $shippingAccount->getId(), $shippingAccount->getChannel()], [static::LOG_CODE, 'CreateShipments']);
        $shipments = $this->createShipmentsForOrdersAndData(
            $orders, $ordersData, $orderParcelsData, $shippingAccount, $rootOu, $courierInstance
        );

        $this->logDebug(static::LOG_BOOK, [$rootOu->getId(), $shippingAccount->getId(), $shippingAccount->getChannel()], [static::LOG_CODE, 'BookShipments']);
        $results = $this->bookShipments($shipments, $orders, $orderLabels, $shippingAccount, $user, $courierInstance);

        $this->logInfo(static::LOG_END, [$rootOu->getId(), $shippingAccount->getId(), $shippingAccount->getChannel(), implode(', ', $orders->getIds())], [static::LOG_CODE, 'End']);
        $this->removeGlobalLogEventParams(['ou', 'account', 'channel']);

        return $results;
    }

    protected function createShipmentsForOrdersAndData(
        OrderCollection $orders,
        array $ordersData,
        array $orderParcelsData,
        Account $shippingAccount,
        OrganisationUnit $rootOu,
        CourierInterface $courierInstance
    ) {
        $shipments = [];
        foreach ($orders as $order) {
            $this->addGlobalLogEventParam('order', $order->getId());

            $orderData = $ordersData[$order->getId()];
            $parcelsData = $orderParcelsData[$order->getId()];

            $deliveryService = $courierInstance->fetchDeliveryServiceByReference($orderData['service']);
            $shipmentClass = $deliveryService->getShipmentClass();
            $packages = null;
            if (is_a($shipmentClass, PackagesInterface::class, true)) {
                $packageClass = call_user_func([$shipmentClass, 'getPackageClass']);
                $packages = $this->createPackagesForOrderParcelData($parcelsData, $shipmentClass, $packageClass);
            }

            $caShipmentData = $this->mapper->ohOrderAndDataToCAShipmentData(
                $order, $orderData, $shippingAccount, $rootOu, $shipmentClass, $packages
            );
            $shipments[$order->getId()] = $deliveryService->createShipment($caShipmentData);

            $this->removeGlobalLogEventParam('order');
        }
        return $shipments;
    }

    protected function createPackagesForOrderParcelData(array $parcelsData, $shipmentClass, $packageClass)
    {
        $packages = [];
        foreach ($parcelsData as $parcelData) {
            $caPackagedata = $this->mapper->ohParcelDataToCAPackageData($parcelData, $shipmentClass, $packageClass);
            $package = call_user_func([$shipmentClass, 'createPackage'], $caPackagedata);
            $packages = [
                $package
            ];
        }
        return $packages;
    }

    protected function bookShipments(
        array $shipments,
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        Account $shippingAccount,
        User $user,
        CourierInterface $courierInstance
    ) {
        $results = [];
        foreach ($shipments as $orderId => $shipment) {
            $this->addGlobalLogEventParam('order', $orderId);

            try {
                $bookedShipment = $courierInstance->bookShipment($shipment);

                $labels = $orderLabels->getBy('orderId', $orderId);
                $labels->rewind();
                $orderLabel = $labels->current();
                $order = $orders->getById($orderId);
                $this->updateOrderLabelFromBookedShipment($orderLabel, $bookedShipment, $courierInstance)
                    ->createOrderTrackingsFromBookedShipment($bookedShipment, $order, $shippingAccount, $user);
                $results[$orderId] = true;

            } catch (UserError $e) {
                $this->logNotice(static::LOG_BOOK_INVALID, [$orderId, $e->getMessage()], [static::LOG_CODE, 'BookInvalid']);
                $exception = new ValidationMessagesException('Validation error');
                $errorCode = ($e->getCode() ?: HttpStatusCode::BAD_REQUEST);
                $exception->addError($e->getMessage(), $orderId . ':' . $errorCode);
                $results[$orderId] = $exception;

            } catch (OperationFailed $e) {
                $this->logWarning(static::LOG_BOOK_ERROR, [$orderId, $e->getMessage()], [static::LOG_CODE, 'BookError']);
                $exception = new ValidationMessagesException('Validation error');
                $errorCode = ($e->getCode() ?: HttpStatusCode::INTERNAL_SERVER_ERROR);
                $exception->addError('There was an unexpected problem creating this shipment', $orderId . ':' . $errorCode);
                $results[$orderId] = $exception;
            }

            $this->removeGlobalLogEventParam('order');
        }

        return $results;
    }

    protected function updateOrderLabelFromBookedShipment(
        OrderLabel $orderLabel,
        ShipmentInterface $bookedShipment,
        CourierInterface $courierInstance
    ) {
        $shipmentLabels = $bookedShipment->getLabels();
        if (empty($shipmentLabels)) {
            $this->logDebug(static::LOG_FETCH_LABELS, [$orderLabel->getOrderId()], [static::LOG_CODE, 'FetchLabels']);
            $shipmentLabels = $this->fetchShipmentLabels($bookedShipment, $courierInstance);
        }
        foreach ($shipmentLabels as $shipmentLabel) {
            if ($shipmentLabel->getType() == LabelInterface::TYPE_PDF) {
                $orderLabel->setLabel($shipmentLabel->getData());
            } elseif ($shipmentLabel->getType() == LabelInterface::TYPE_PNG) {
                $orderLabel->setImage($shipmentLabel->getData());
            }
        }

        if ($orderLabel->getLabel() && !$orderLabel->getImage()) {
            $this->setOrderLabelImageFromPDF($orderLabel);
        } elseif ($orderLabel->getImage() && !$orderLabel->getLabel()) {
            $this->setOrderLabelPDFFromImage($orderLabel);
        }

        $orderLabel->setExternalId($bookedShipment->getCourierReference())
            ->setStatus(OrderLabelStatus::NOT_PRINTED)
            ->setCreated((new StdlibDateTime())->stdFormat());
        
        $this->orderLabelService->save($orderLabel);

        return $this;
    }

    protected function fetchShipmentLabels(ShipmentInterface $shipment, CourierInterface $courierInstance)
    {
        if (!$courierInstance instanceof ShipmentFetchingInterface) {
            throw new \RuntimeException('Request to fetch shipment labels but the courier instance does not support it');
        }
        $fetchedShipment = $courierInstance->fetchShipment($shipment);
        if (empty($fetchedShipment->getLabels())) {
            throw new NotFound('No labels found for shipment');
        }
        return $fetchedShipment->getLabels();
    }

    protected function setOrderLabelImageFromPDF(OrderLabel $orderLabel)
    {
        // Do this as a background job as we don't need the image until invoices are generated
        $workload = new OrderLabelPdfToPngWorkload($orderLabel->getId());
        $this->gearmanClient->doBackground(OrderLabelPdfToPngWorkerFunction::FUNCTION_NAME, serialize($workload));
    }

    protected function setOrderLabelPDFFromImage(OrderLabel $orderLabel)
    {
        // Do this immediately as we need PDF labels straight away
        $converter = $this->pngToPdfConverter;
        $converter($orderLabel);
    }

    protected function createOrderTrackingsFromBookedShipment(
        ShipmentInterface $shipment,
        Order $order,
        Account $shippingAccount,
        User $user
    ) {
        if (empty($shipment->getTrackingReferences())) {
            $this->logDebug(static::LOG_NO_TRACKING, [$order->getId(), $order->getOrganisationUnitId()], [static::LOG_CODE, 'NoTracking']);
            return;
        }
        foreach ($shipment->getTrackingReferences() as $trackingReference) {
            $date = new StdlibDateTime();
            $trackingData = [
                'organisationUnitId' => $order->getOrganisationUnitId(),
                'orderId' => $order->getId(),
                'userId' => $user->getId(),
                'timestamp' => $date->stdFormat(),
                'carrier' => $shippingAccount->getDisplayChannel(),
                'number' => $trackingReference,
            ];
            $orderTracking = $this->orderTrackingMapper->fromArray($trackingData);
            $this->logDebug(static::LOG_TRACKING, [$order->getId(), $order->getOrganisationUnitId(), $trackingReference], [static::LOG_CODE, 'Tracking']);
            $this->orderTrackingService->save($orderTracking);
        }

        // Update the sales channels
        $this->orderTrackingService->createGearmanJob($order);
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function setOrderTrackingMapper(OrderTrackingMapper $orderTrackingMapper)
    {
        $this->orderTrackingMapper = $orderTrackingMapper;
        return $this;
    }

    protected function setOrderTrackingService(OrderTrackingService $orderTrackingService)
    {
        $this->orderTrackingService = $orderTrackingService;
        return $this;
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }

    protected function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    protected function setPngToPdfConverter(PngToPdfConverter $pngToPdfConverter)
    {
        $this->pngToPdfConverter = $pngToPdfConverter;
        return $this;
    }
}
