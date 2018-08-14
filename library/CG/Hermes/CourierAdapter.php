<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\Hermes\Credentials\FormFactory as CredentialsFormFactory;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;
use CG\Hermes\Shipment\Service as ShipmentService;
use Psr\Log\LoggerInterface;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface
{
    const FEATURE_FLAG = 'Hermes Corporate';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentService */
    protected $shipmentService;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService,
        ShipmentService $shipmentService
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentService = $shipmentService;
    }

    /**
     * @inheritdoc
     */
    public function getCredentialsForm()
    {
        return ($this->credentialsFormFactory)();
    }

    /**
     * @inheritdoc
     */
    public function bookShipment(ShipmentInterface $shipment)
    {
        $this->logger->debug('Booking Hermes shipment for Account {account}', ['account' => $shipment->getAccount()->getId()]);
        return $this->shipmentService->bookShipment($shipment);
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServices()
    {
        return $this->deliveryServiceService->getDeliveryServices();
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServiceByReference($reference)
    {
        return $this->deliveryServiceService->getDeliveryServiceByReference($reference);
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForAccount(Account $account)
    {
        return $this->fetchDeliveryServices();
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForAccountAndCountry(Account $account, $isoAlpha2CountryCode)
    {
        return $this->deliveryServiceService->getDeliveryServicesForCountry($isoAlpha2CountryCode);
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        return $this->deliveryServiceService->getDeliveryServicesForCountry(
            $shipment->getDeliveryAddress()->getISOAlpha2CountryCode()
        );
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function cancelShipment(ShipmentInterface $shipment)
    {
        $this->logger->debug('Cancelling Hermes shipment for Account {account} (no request required)', ['account' => $shipment->getAccount()->getId()]);
        // Hermes labels aren't charged for until they're scanned so we don't need to make any calls
        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateShipment(ShipmentInterface $shipment)
    {
        $this->cancelShipment($shipment);
        $this->bookShipment($shipment);
    }
}