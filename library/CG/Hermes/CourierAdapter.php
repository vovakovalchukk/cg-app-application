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
use Psr\Log\LoggerInterface;
use Zend\Form\Form as ZendForm;

class CourierAdapter implements CourierInterface, LocalAuthInterface, CredentialRequestInterface, EmailClientAwareInterface
{
    const FEATURE_FLAG = 'Hermes Corporate';

    /** @var CredentialsFormFactory */
    protected $credentialsFormFactory;
    /** @var CredentialsRequester */
    protected $credentialsRequester;

    /** @var LoggerInterface */
    protected $logger;
    /** @var EmailClientInterface */
    protected $emailClient;

    public function __construct(
        CredentialsFormFactory $credentialsFormFactory,
        CredentialsRequester $credentialsRequester
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;
        $this->credentialsRequester = $credentialsRequester;
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
        return $this->credentialsFormFactory->getCredentialsRequestForm($accountHolderAddress, $accountHolderCompanyName);
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