<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\RoyalMailApi\Credentials\FormFactory as CredentialsFormFactory;
use Psr\Log\LoggerInterface;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface
{
    const FEATURE_FLAG = 'Royal Mail API';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(CredentialsFormFactory $credentialsFormFactory)
    {
        $this->credentialsFormFactory = $credentialsFormFactory;
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
        // TODO in TAC-374
    }

    /**
     * @inheritdoc
     */
    public function fetchDeliveryServiceByReference($reference)
    {
        // TODO in TAC-374
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
        // TODO in TAC-374
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