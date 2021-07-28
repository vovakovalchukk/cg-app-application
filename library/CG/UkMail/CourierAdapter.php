<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialRequest\TestPackFile;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\UkMail\Credentials\FormFactory as CredentialsFormFactory;
use CG\UkMail\DeliveryService\Service as DeliveryServiceService;
use CG\UkMail\Shipment\Service as ShipmentService;
use Psr\Log\LoggerInterface;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface, ConfigInterface, TestPackInterface
{
    public const FEATURE_FLAG = 'UK Mail DHL Parcel UK';

    /** @var LoggerInterface */
    protected $logger;
    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentService */
    protected $shipmentService;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService,
        ShipmentService $shipmentService
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentService = $shipmentService;
    }

    public function bookShipment(ShipmentInterface $shipment)
    {
        return $this->shipmentService->bookShipment($shipment);
    }

    public function cancelShipment(ShipmentInterface $shipment)
    {
        return $this->shipmentService->cancelShipment($shipment);
    }

    public function updateShipment(ShipmentInterface $shipment)
    {
        $this->cancelShipment($shipment);
        $this->bookShipment($shipment);
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
        return $this->deliveryServiceService->getDeliveryServicesForCountry($isoAlpha2CountryCode);
    }

    public function fetchDeliveryServicesForShipment(ShipmentInterface $shipment)
    {
        return $this->deliveryServiceService->getDeliveryServicesForCountry(
            $shipment->getDeliveryAddress()->getISOAlpha2CountryCode()
        );
    }

    public function getCredentialsForm()
    {
        return $this->credentialsFormFactory->getCredentialsForm();
    }

    public function getConfigForm()
    {
        return $this->credentialsFormFactory->getConfigForm();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function isAccountInTestMode(Account $account)
    {
        // TODO: Implement isAccountInTestMode() method.
    }

    public function getTestModeInstructions()
    {
        // TODO: Implement getTestModeInstructions() method.
    }

    public function getTestPackFileList()
    {
        // TODO: Implement getTestPackFileList() method.
    }

    public function generateTestPackFile(TestPackFile $file, Account $account, AddressInterface $collectionAddress)
    {
        // TODO: Implement generateTestPackFile() method.
    }
}