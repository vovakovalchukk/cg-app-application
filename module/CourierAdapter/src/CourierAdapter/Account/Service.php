<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Service as OHAccountService;
use CG\Account\Credentials\Cryptor;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\Http\Exception\Exception3xx\NotModified;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Form\Form as ZendForm;

class Service
{
    use PrepareAdapterImplementationFieldsTrait;

    /** @var OHAccountService */
    protected $ohAccountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        OHAccountService $ohAccountService,
        Cryptor $cryptor,
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setOHAccountService($ohAccountService)
            ->setCryptor($cryptor)
            ->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper);
    }

    /**
     * @return CourierInterface
     */
    public function getCourierInstanceForChannel($channelName, $specificInterface = null)
    {
        if (!$this->adapterImplementationService->isProvidedChannel($channelName)) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but that is not a channel provided by the Courier Adapters');
        }
        $adapterImplementation = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstance($adapterImplementation);
        if ($specificInterface && !$courierInstance instanceof $specificInterface) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but its adapter does not implement ' . $specificInterface);
        }
        return $courierInstance;
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
    public function validateSetupForm(ZendForm $form, CourierInterface $courierInstance)
    {
        if (!$form->isValid()) {
            return false;
        }

        if ($courierInstance instanceof CredentialVerificationInterface) {
            $caAccount = $this->caAccountMapper->fromArray([
                'credentials' => $form->getData(),
            ]);
            return $courierInstance->validateCredentials($caAccount);
        }

        return true;
    }

    public function saveConfigForAccount($accountId, array $config)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->getCourierInstanceForChannel($account->getChannel(), ConfigInterface::class);

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
     * @return string dataUri
     */
    public function generateTestPackFileDataForAccount($accountId, $fileReference)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->getCourierInstanceForChannel($account->getChannel(), TestPackInterface::class);

        $testPackFileToGenerate = null;
        foreach ($courierInstance->getTestPackFileList() as $testPackFile) {
            if ($testPackFile->getReference() == $fileReference) {
                $testPackFileToGenerate = $testPackFile;
                break;
            }
        }
        if (!$testPackFileToGenerate) {
            throw new InvalidArgumentException('No test pack file with reference "' . $fileReference . '" found');
        }

        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        return $courierInstance->generateTestPackFile($testPackFileToGenerate, $caAccount);
    }

    protected function setOHAccountService(OHAccountService $ohAccountService)
    {
        $this->ohAccountService = $ohAccountService;
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
}