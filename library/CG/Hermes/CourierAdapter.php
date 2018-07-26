<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\EmailClientAwareInterface;
use CG\CourierAdapter\EmailClientInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\ShipmentInterface;
use CG\Hermes\Credentials\FormFactory as CredentialsFormFactory;
use CG\Hermes\Credentials\Requester as CredentialsRequester;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;
use Psr\Log\LoggerInterface;
use Zend\Form\Form as ZendForm;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CredentialRequestInterface, EmailClientAwareInterface
{
    const FEATURE_FLAG = 'Hermes Corporate';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var CredentialsRequester */
    protected $credentialsRequester;
    /** @var DeliveryServiceService */
    protected $deliveryServiceService;

    /** @var LoggerInterface */
    protected $logger;
    /** @var EmailClientInterface */
    protected $emailClient;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        CredentialsRequester $credentialsRequester,
        DeliveryServiceService $deliveryServiceService
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->credentialsRequester = $credentialsRequester;
        $this->deliveryServiceService = $deliveryServiceService;
    }

    /**
     * @inheritdoc
     */
    public function getCredentialsForm()
    {
        return $this->credentialsFormFactory->getCredentialsForm();
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
    public function getCredentialsRequestInstructions()
    {
        $instructions = <<<EOS
<h1>Connecting Hermes with ChannelGrabber</h1>
<ol style="list-style-type: decimal">
    <li>Fill in the form below then click "Submit Request".</li>
    <li>You will receive test credentials from Hermes which you can use to test the integration. Enter these into ChannelGrabber by going to Settings -> Shipping Channels -> Hermes Account and enabling the account by clicking the Off toggle above to turn On. Enter your credentials in the resulting form. Leave the "live credentials" and "test pack approved" checkboxes unchecked at this stage.</li>
EOS;
        $instructions .= $this->getTestPackInstructionsPartial();
        $instructions .= '</ol>';
        return $instructions;
    }

    /**
     * @inheritdoc
     */
    public function getAccountPendingInstructions()
    {
        $instructions = <<<EOS
<h1 style="float:none">Connecting Hermes with ChannelGrabber</h1>
<ol style="list-style-type: decimal">
    <li>Once you receive test credentials from Hermes enter these into ChannelGrabber by going to Settings -> Shipping Channels -> Hermes Account and enabling the account by clicking the Off toggle above to turn On. Enter your credentials in the resulting form. Leave the "live credentials" and "test pack approved" checkboxes unchecked at this stage.</li>
EOS;
        $instructions .= $this->getTestPackInstructionsPartial();
        $instructions .= '</ol>';
        return $instructions;
    }

    protected function getTestPackInstructionsPartial(): string
    {
        return <<<EOS
    <li>While in test mode you will see a PDF file listed on the account page called 'TEST_PACK_LABELS.pdf' that you need to download. These should then be sent to Hermes for approval which can be done via email at this stage.</li>
    <li>You will receive live credentials once Hermes has received and assessed the quality of the labels. Add your live credentials by going to Settings -> Shipping Channels -> Hermes Account and clicking "Renew Connection". Make sure to also check the box to say they're live credentials but do not tick the box to say the live test pack has been approved.</li>
    <li>You will need to download the test pack again but this time it will have been generated against Hermes live servers. This time the test pack requires printing using the label printer you intend to use for Hermes labels. Send these in the post to Hermes for approval.</li>
    <li>Once Hermes let you know the live test pack is approved go to Settings -> Shipping Channels -> Hermes Account and click "Renew Connection" once more. This time tick the "live test pack approved" box.</li>
    <li>Your Hermes account will now be available to use within ChannelGrabber.</li>
EOS;

    }

    /**
     * @inheritdoc
     */
    public function getCredentialsRequestForm(AddressInterface $accountHolderAddress, $accountHolderCompanyName)
    {
        return $this->credentialsFormFactory->getCredentialsRequestForm();
    }

    /**
     * @inheritdoc
     */
    public function submitCredentialsRequestForm(ZendForm $credentialsRequestForm)
    {
        ($this->credentialsRequester)($credentialsRequestForm, $this->emailClient, $this->logger);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setEmailClient(EmailClientInterface $emailClient)
    {
        $this->emailClient = $emailClient;
    }
}