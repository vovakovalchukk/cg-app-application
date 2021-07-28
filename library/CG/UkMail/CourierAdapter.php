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
use CG\UkMail\Credentials\Request\TestPackGenerator;
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
    /** @var TestPackGenerator */
    protected $testPackGenerator;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        DeliveryServiceService $deliveryServiceService,
        ShipmentService $shipmentService,
        TestPackGenerator $testPackGenerator
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentService = $shipmentService;
        $this->testPackGenerator = $testPackGenerator;
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
        $credentials = $account->getCredentials();

        if (isset($credentials['live']) && $credentials['live']) {
            return false;
        }

        return true;
    }

    public function getTestModeInstructions()
    {
        return <<<EOS
<h1 style="float:none">Connecting UKMail with ChannelGrabber</h1>
<ol style="list-style-type: decimal">
    <li>While in test mode you will see a PDF file listed on the account page called 'TEST_PACK_LABELS.pdf' that you need to download. These should then be sent to UKMail for approval.</li>
    <li>You will receive live credentials once UKMail has received and assessed the quality of the labels. Add your live credentials by going to Settings -> Shipping Channels -> UKMail Account and clicking "Renew Connection". Make sure to also check the box to say they're live credentials.</li>
    <li>Your UKMail account will now be available to use within ChannelGrabber.</li>
</ol>
EOS;
    }

    public function getTestPackFileList()
    {
        return $this->testPackGenerator->getTestPackFileList();
    }

    public function generateTestPackFile(TestPackFile $file, Account $account, AddressInterface $collectionAddress)
    {
        return $this->testPackGenerator->generateTestPackFile($file, $account, $collectionAddress);
    }
}