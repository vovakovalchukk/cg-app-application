<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Service as OHAccountService;
use CG\Account\Credentials\Cryptor;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\Http\Exception\Exception3xx\NotModified;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;

class Service
{
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
    public function validateSetupFields(array $fields, array $values, CourierInterface $courierInstance)
    {
        if ($courierInstance instanceof CredentialVerificationInterface) {
            $caAccount = $this->caAccountMapper->fromArray([
                'credentials' => $values,
            ]);
            return $courierInstance->validateCredentials($caAccount);
        }

        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field->getOption('required') && (!isset($values[$field->getName()]) || $values[$field->getName()] == '')) {
                return false;
            }
        }
        return true;
    }

    public function saveConfigForAccount($accountId, array $config)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->getCourierInstanceForChannel($account->getChannel(), ConfigInterface::class);

        $externalData = $account->getExternalData();
        $externalDataConfig = (isset($externalData['config']) ? json_decode($externalData['config'], true) : []);
        foreach ($courierInstance->getConfigFields() as $field) {
            $value = (isset($config[$field->getName()]) ? $config[$field->getName()] : null);
            $externalDataConfig[$field->getName()] = $value;
        }
        $externalData['config'] = json_encode($externalDataConfig);

        try {
            $account->setExternalData($externalData);
            $this->ohAccountService->save($account);
        } catch (NotModified $e) {
            // No-op
        }
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