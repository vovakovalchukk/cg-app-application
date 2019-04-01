<?php
namespace CG\Intersoft\RoyalMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\Intersoft\RoyalMail\DeliveryService\Service as DeliveryServiceService;
use CG\Intersoft\Credentials\FormFactory as CredentialsFormFactory;
use CG\Intersoft\RoyalMail\Shipment\Booker as ShipmentBooker;
use Psr\Log\LoggerInterface;
use CG\Intersoft\RoyalMail\Shipment\Canceller as ShipmentCanceller;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface
{
    const FEATURE_FLAG = 'Royal Mail Intersoft';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;

    /** @var LoggerInterface */
    protected $logger;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentBooker */
    protected $shipmentBooker;
    /** @var ShipmentCanceller */
    protected $shipmentCanceller;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService,
        ShipmentBooker $shipmentBooker,
        ShipmentCanceller $shipmentCanceller
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentBooker = $shipmentBooker;
        $this->shipmentCanceller = $shipmentCanceller;
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
        $this->logger->debug('Booking Royal Mail Intersoft shipment for Account {account}', ['account' => $shipment->getAccount()->getId()]);
        return ($this->shipmentBooker)($shipment);
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServices()
    {
        $this->deliveryServiceService->getDeliveryServices();
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
        return $this->deliveryServiceService->getDeliveryServices();
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

    /**
     * @inheritdoc
     */
    public function cancelShipment(ShipmentInterface $shipment)
    {
        $this->logger->debug('Cancelling Royal Mail Intersoft shipment for order {order} and Account {account}', ['order' => $shipment->getCustomerReference(), 'account' => $shipment->getAccount()->getId()]);
        ($this->shipmentCanceller)($shipment);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateShipment(ShipmentInterface $shipment)
    {
        // TODO in TAC-386
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}