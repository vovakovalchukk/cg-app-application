<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Mapper as OHAccountMapper;
use CG\Account\Client\Service as OHAccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\AccountFactory as AccountSetupFactory;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\Http\Exception\Exception3xx\NotModified;
use Zend\Form\Form as ZendForm;

class Service
{
    use PrepareAdapterImplementationFieldsTrait;

    /** @var OHAccountService */
    protected $ohAccountService;
    /** @var OHAccountMapper */
    protected $ohAccountMapper;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var AccountSetupFactory */
    protected $accountSetupFactory;

    public function __construct(
        OHAccountService $ohAccountService,
        OHAccountMapper $ohAccountMapper,
        Cryptor $cryptor,
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper,
        AccountSetupFactory $accountSetupFactory
    ) {
        $this->setOHAccountService($ohAccountService)
            ->setOhAccountMapper($ohAccountMapper)
            ->setCryptor($cryptor)
            ->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper)
            ->setAccountSetupFactory($accountSetupFactory);
    }

    /**
     * @return array
     */
    public function getCredentialsArrayForAccount($accountId)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->toArray();
    }

    /**
     * @return bool
     */
    public function validateSetupForm(ZendForm $form, CourierInterface $courierInstance, $accountId = null)
    {
        if (!$form->isValid()) {
            return false;
        }

        if (!$courierInstance instanceof CredentialVerificationInterface) {
            return true;
        }

        $caAccountData = ['credentials' => $form->getData()];
        if ($accountId) {
            $account = $this->ohAccountService->fetch($accountId);
            $caAccountData['id'] = $account->getExternalId();
        }

        $caAccount = $this->caAccountMapper->fromArray($caAccountData);
        return $courierInstance->validateCredentials($caAccount);
    }

    public function saveConfigForAccount($accountId, array $config)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $account->getChannel(), ConfigInterface::class
        );

        $form = $courierInstance->getConfigForm();
        $this->prepareAdapterImplementationFormForSubmission($form, $config);

        if (!$form->isValid()) {
            return $form->getMessages();
        }
        $formData = $form->getData();

        $externalData = $account->getExternalData();
        $externalData['config'] = json_encode($formData);

        try {
            $account->setExternalData($externalData);
            $this->ohAccountService->save($account);
        } catch (NotModified $e) {
            // No-op
        }
        return true;
    }

    /**
     * @return string
     */
    public function getCredentialsUriForNewAccount($channelName, $organisationUnitId)
    {
        $account = $this->getNewAccount($channelName, $organisationUnitId);
        $routeVariables = [AccountCreationService::REQUEST_CREDENTIALS_SKIPPED_FIELD => true];
        return $this->accountSetupFactory->createRedirect($account, '', $routeVariables);
    }

    protected function getNewAccount($channelName, $organisationUnitId)
    {
        return $this->ohAccountMapper->fromArray([
            'channel' => $channelName,
            'organisationUnitId' => $organisationUnitId,
            'displayName' => '',
            'credentials' => '',
            'active' => true,
            'deleted' => false,
            'type' => [ChannelType::SHIPPING]
        ]);
    }

    protected function setOHAccountService(OHAccountService $ohAccountService)
    {
        $this->ohAccountService = $ohAccountService;
        return $this;
    }

    protected function setOhAccountMapper(OHAccountMapper $ohAccountMapper)
    {
        $this->ohAccountMapper = $ohAccountMapper;
        return $this;
    }

    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }

    public function setAccountSetupFactory(AccountSetupFactory $accountSetupFactory)
    {
        $this->accountSetupFactory = $accountSetupFactory;
        return $this;
    }
}