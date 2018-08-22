<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\CredentialRequest\TestPackFile;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\Hermes\Credentials\FormFactory as CredentialsFormFactory;
use CG\Hermes\Credentials\Request\TestPackGenerator;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;
use CG\Hermes\Shipment\Service as ShipmentService;
use Psr\Log\LoggerInterface;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CancellingInterface, TestPackInterface
{
    const FEATURE_FLAG = 'Hermes Corporate';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentService */
    protected $shipmentService;
    /** @var TestPackGenerator */
    protected $testPackGenerator;

    /** @var LoggerInterface */
    protected $logger;

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
        $this->logger->debug('Booking Hermes shipment for Account {account}', ['account' => $shipment->getAccount()->getId()]);
        return $this->shipmentService->bookShipment($shipment);
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
        return $this->fetchDeliveryServices();
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
    public function getTestModeInstructions()
    {
        return <<<EOS
<h1 style="float:none">Connecting Hermes with ChannelGrabber</h1>
<ol style="list-style-type: decimal">
    <li>While in test mode you will see a PDF file listed on the account page called 'TEST_PACK_LABELS.pdf' that you need to download. These should then be sent to Hermes for approval which can be done via email at this stage.</li>
    <li>You will receive live credentials once Hermes has received and assessed the quality of the labels. Add your live credentials by going to Settings -> Shipping Channels -> Hermes Account and clicking "Renew Connection". Make sure to also check the box to say they're live credentials but do not tick the box to say the live test pack has been approved.</li>
    <li>You will need to download the test pack again but this time it will have been generated against Hermes live servers. This time the test pack requires printing using the label printer you intend to use for Hermes labels. Send these in the post to Hermes for approval.</li>
    <li>Once Hermes let you know the live test pack is approved go to Settings -> Shipping Channels -> Hermes Account and click "Renew Connection" once more. This time tick the "live test pack approved" box.</li>
    <li>Your Hermes account will now be available to use within ChannelGrabber.</li>
</ol>
EOS;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function cancelShipment(ShipmentInterface $shipment)
    {
        $this->logger->debug('Cancelling Hermes shipment for Account {account} (no request required)', ['account' => $shipment->getAccount()->getId()]);
        // Hermes labels aren't charged for until they're scanned so we don't need to make any calls
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
    public function isAccountInTestMode(Account $account)
    {
        $credentials = $account->getCredentials();
        // Accounts are considered to be in test mode until they have live credentials AND
        // the live test pack has been approved by Hermes
        if (isset($credentials['liveCredentials'], $credentials['testPackApproved']) &&
            $credentials['liveCredentials'] &&
            $credentials['testPackApproved']
        ) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTestPackFileList()
    {
        return $this->testPackGenerator->getTestPackFileList();
    }

    /**
     * @inheritdoc
     */
    public function generateTestPackFile(TestPackFile $file, Account $account, AddressInterface $collectionAddress)
    {
        return $this->testPackGenerator->generateTestPackFile($file, $account, $collectionAddress);
    }
}