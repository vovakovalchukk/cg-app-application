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

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        CredentialsValidator $credentialsValidator,
        DeliveryServiceService $deliveryServiceService,
        ShipmentBooker $shipmentBooker
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->credentialsValidator = $credentialsValidator;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentBooker = $shipmentBooker;
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
        $this->deliveryServiceService->getDeliveryServiceByReference($reference);
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForAccount(Account $account)
    {
        // TODO in TAC-374
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForAccountAndCountry(Account $account, $isoAlpha2CountryCode)
    {
        return $this->deliveryServiceService->getDeliveryServices();
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        // TODO in TAC-374
    }

    /**
     * @inheritdoc
     */
    public function cancelShipment(ShipmentInterface $shipment)
    {
        // TODO in TAC-375
    }

    /**
     * @inheritdoc
     */
    public function updateShipment(ShipmentInterface $shipment)
    {
        // TODO in TAC-375
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}