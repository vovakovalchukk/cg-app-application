<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\ShipmentInterface;
use CG\Hermes\Credentials\FormFactory as CredentialsFormFactory;
use Psr\Log\LoggerInterface;

class CourierAdapter implements CourierInterface, LocalAuthInterface
{
    const FEATURE_FLAG = 'Hermes Corporate';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(CredentialsFormFactory $credentialsFormFactory)
    {
        $this->credentialsFormFactory = $credentialsFormFactory;
    }

    /**
     * This will return the fields that the application will need to populate to connect an account.
     * This will likely include things like access token, or username and password. Any data provided by the user for
     * these fields will be treated as sensitive and be encrypted for storage.
     *
     * @return \Zend\Form\Form
     */
    public function getCredentialsForm()
    {
        return ($this->credentialsFormFactory)();
    }

    /**
     * Book a shipment with the courier.
     * This should populate the shipment's courierReference, labels and trackingReference fields for the app to make use of.
     *
     * @throws OperationFailed on system error e.g. unable to connect to the courier
     * @throws UserError on invalid shipment data e.g. weight too high or invalid postcode
     * @return ShipmentInterface
     */
    public function bookShipment(ShipmentInterface $shipment)
    {
        // To be implmented in TAC-172
    }

    /**
     * This will fetch all available services for the courier
     *
     * @throws OperationFailed
     * @return DeliveryServiceInterface[]
     */
    public function fetchDeliveryServices()
    {
        // To be implmented in TAC-171
    }

    /**
     * This will fetch a delivery service by it's reference
     *
     * @param string $reference The reference for the delivery service to be fetched
     * @throws NotFound
     * @return DeliveryServiceInterface
     */
    public function fetchDeliveryServiceByReference($reference)
    {
        // To be implmented in TAC-171
    }

    /**
     * This will fetch all available services for an account.
     * If the courier doesn't provide per account services, this should wrap self::fetchDeliveryServices().
     *
     * @throws OperationFailed
     * @return DeliveryServiceInterface[]
     */
    public function fetchDeliveryServicesForAccount(Account $account)
    {
        // To be implmented in TAC-171
    }

    /**
     * This will fetch all available services for an account and country code.
     *
     * @param Account $account
     * @param string $isoAlpha2CountryCode
     * @throws OperationFailed
     * @return DeliveryServiceInterface[]
     */
    public function fetchDeliveryServicesForAccountAndCountry(Account $account, $isoAlpha2CountryCode)
    {
        // To be implmented in TAC-171
    }

    /**
     * This will fetch all available services for a shipment. This will usually be an order, or part there of. If not
     * explicitly supported, wrap self::fetchDeliveryServicesForAccount() passing the account from $shipment
     *
     * @throws OperationFailed
     * @return DeliveryServiceInterface[]
     */
    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        // To be implmented in TAC-171
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}