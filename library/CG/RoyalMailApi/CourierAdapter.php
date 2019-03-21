<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\RoyalMailApi\Credentials\FormFactory as CredentialsFormFactory;
use CG\RoyalMailApi\Credentials\Validator as CredentialsValidator;
use CG\RoyalMailApi\Shipment\Booker as ShipmentBooker;
use CG\RoyalMailApi\Shipment\Canceller as ShipmentCanceller;
use Psr\Log\LoggerInterface;
use CG\RoyalMailApi\DeliveryService\Service as DeliveryServiceService;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CredentialVerificationInterface, CancellingInterface
{
    const FEATURE_FLAG = 'Royal Mail API';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var CredentialsValidator */
    protected $credentialsValidator;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentBooker */
    protected $shipmentBooker;
    /** @var ShipmentCanceller */
    protected $shipmentCanceller;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        CredentialsValidator $credentialsValidator,
        DeliveryServiceService $deliveryServiceService,
        ShipmentBooker $shipmentBooker,
        ShipmentCanceller $shipmentCanceller
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->credentialsValidator = $credentialsValidator;
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
    public function validateCredentials(Account $account)
    {
        return ($this->credentialsValidator)($account);
    }

    /**
     * @inheritdoc
     */
    public function bookShipment(ShipmentInterface $shipment)
    {
        $this->logger->debug('Booking Royal Mail API shipment for Account {account}', ['account' => $shipment->getAccount()->getId()]);
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
        $this->logger->debug('Cancelling Royal Mail API shipment for order {order} and Account {account}', ['order' => $shipment->getCustomerReference(), 'account' => $shipment->getAccount()->getId()]);
        ($this->shipmentCanceller)($shipment);
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

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}