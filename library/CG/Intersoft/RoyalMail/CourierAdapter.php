<?php
namespace CG\Intersoft\RoyalMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Manifest\GeneratingInterface as ManifestGeneratingInterface;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\Intersoft\Credentials\FormFactory as CredentialsFormFactory;
use CG\Intersoft\Manifest\Generator as ManifestGenerator;
use CG\Intersoft\RoyalMail\DeliveryService\Service as DeliveryServiceService;
use CG\Intersoft\RoyalMail\Shipment\Booker as ShipmentBooker;
use Psr\Log\LoggerInterface;
use CG\Intersoft\RoyalMail\Shipment\Canceller as ShipmentCanceller;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface, ManifestGeneratingInterface
{
    const CARRIER_CODE = 'RMG';
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
    /** @var ManifestGenerator */
    protected $manifestGenerator;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService,
        ShipmentBooker $shipmentBooker,
        ShipmentCanceller $shipmentCanceller,
        ManifestGenerator $manifestGenerator
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentBooker = $shipmentBooker;
        $this->shipmentCanceller = $shipmentCanceller;
        $this->manifestGenerator = $manifestGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getCredentialsForm()
    {
        return ($this->credentialsFormFactory)();
    }

    public function getFirstTimeAccountForm()
    {
        return $this->credentialsFormFactory->getFirstTimeForm();
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
        $this->cancelShipment($shipment);
        $this->bookShipment($shipment);
    }

    /**
     * @inheritDoc
     */
    public function generateManifest(Account $account)
    {
        $this->logger->debug('Generating manifest for account {account}', ['account' => $account->getId()]);
        return ($this->manifestGenerator)($account);
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}