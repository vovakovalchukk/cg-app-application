<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\Manifest\GeneratingInterface as ManifestGeneratingInterface;
use CG\CourierAdapter\Manifest\ManifestInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use Psr\Log\LoggerInterface;
use CG\UkMail\Credentials\FormFactory as CredentialsFormFactory;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface, ManifestGeneratingInterface
{
    public const FEATURE_FLAG = 'UK Mail DHL Parcel UK';

    protected $credentialsFormFactory;

    public function __construct(CredentialsFormFactory $credentialsFormFactory)
    {
        $this->credentialsFormFactory = $credentialsFormFactory;
    }

    public function bookShipment(ShipmentInterface $shipment)
    {
        // TODO: Implement bookShipment() method.
    }

    public function cancelShipment(ShipmentInterface $shipment)
    {
        // TODO: Implement cancelShipment() method.
    }

    public function fetchDeliveryServices()
    {
        // TODO: Implement fetchDeliveryServices() method.
    }

    public function fetchDeliveryServiceByReference($reference)
    {
        // TODO: Implement fetchDeliveryServiceByReference() method.
    }

    public function fetchDeliveryServicesForAccount(Account $account)
    {
        // TODO: Implement fetchDeliveryServicesForAccount() method.
    }

    public function fetchDeliveryServicesForAccountAndCountry(Account $account, $isoAlpha2CountryCode)
    {
        // TODO: Implement fetchDeliveryServicesForAccountAndCountry() method.
    }

    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        // TODO: Implement fetchDeliveryServicesForShipment() method.
    }

    public function generateManifest(Account $account)
    {
        // TODO: Implement generateManifest() method.
    }

    public function getCredentialsForm()
    {
        return ($this->credentialsFormFactory)();
    }

    public function setLogger(LoggerInterface $logger)
    {
        // TODO: Implement setLogger() method.
    }

    public function updateShipment(ShipmentInterface $shipment)
    {
        // TODO: Implement updateShipment() method.
    }
}