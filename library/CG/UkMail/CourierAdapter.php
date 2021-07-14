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
use CG\UkMail\DeliveryService\Service as DeliveryServiceService;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface, ManifestGeneratingInterface
{
    public const FEATURE_FLAG = 'UK Mail DHL Parcel UK';

    protected const COUNTRY_CODE_GB = 'GB';

    /** @var LoggerInterface */
    protected $logger;
    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
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
        return $this->deliveryServiceService->getDeliveryServices();
    }

    public function fetchDeliveryServiceByReference($reference)
    {
        return $this->deliveryServiceService->getDeliveryServiceByReference($reference);
    }

    public function fetchDeliveryServicesForAccount(Account $account)
    {
        return $this->deliveryServiceService->getDeliveryServices();
    }

    public function fetchDeliveryServicesForAccountAndCountry(Account $account, $isoAlpha2CountryCode)
    {
        return $this->deliveryServiceService->getDeliveryServicesForCountry();
    }

    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        if ($shipment->getDeliveryAddress()->getISOAlpha2CountryCode() != static::COUNTRY_CODE_GB) {
            return $this->deliveryServiceService->getDeliveryServicesForCountry();
        }

        return $this->deliveryServiceService->getDomesticDeliveryServices();
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
        $this->logger = $logger;
    }

    public function updateShipment(ShipmentInterface $shipment)
    {
        // TODO: Implement updateShipment() method.
    }
}