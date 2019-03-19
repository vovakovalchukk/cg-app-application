<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\RoyalMailApi\Credentials\FormFactory as CredentialsFormFactory;
use Psr\Log\LoggerInterface;
use CG\RoyalMailApi\DeliveryService\Service as DeliveryServiceService;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface
{
    const FEATURE_FLAG = 'Royal Mail API';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(CredentialsFormFactory $credentialsFormFactory, DeliveryServiceService $deliveryServiceService)
    {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
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
        // TODO in TAC-375
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
        return $this->deliveryServiceService->getDeliveryServices();
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        return $this->deliveryServiceService->getDeliveryServices();
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