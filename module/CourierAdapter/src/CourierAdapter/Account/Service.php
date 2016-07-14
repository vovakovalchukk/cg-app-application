<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Service as OHAccountService;
use CG\Account\Credentials\Cryptor;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use CG\Http\Exception\Exception3xx\NotModified;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;

class Service
{
    /** @var OHAccountService */
    protected $ohAccountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AdapterService */
    protected $adapterService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        OHAccountService $ohAccountService,
        Cryptor $cryptor,
        AdapterService $adapterService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setOHAccountService($ohAccountService)
            ->setCryptor($cryptor)
            ->setAdapterService($adapterService)
            ->setCAAccountMapper($caAccountMapper);
    }

    public function getCourierInterfaceForChannel($channelName, $specificInterface = null)
    {
        if (!$this->adapterService->isProvidedChannel($channelName)) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but that is not a channel provided by the Courier Adapters');
        }
        $adapter = $this->adapterService->getAdapterByChannelName($channelName);
        $courierInterface = $this->adapterService->getAdapterCourierInterface($adapter);
        if ($specificInterface && !$courierInterface instanceof $specificInterface) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but its adapter does not implement ' . $specificInterface);
        }
        return $courierInterface;
    }

    public function getCredentialsArrayForAccount($accountId)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->toArray();
    }

    public function validateSetupFields(array $fields, array $values, CourierInterface $courierInterface)
    {
        if ($courierInterface instanceof CredentialVerificationInterface) {
            $caAccount = $this->caAccountMapper->fromArray([
                'credentials' => $values,
            ]);
            return $courierInterface->validateCredentials($caAccount);
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
        $courierInterface = $this->getCourierInterfaceForChannel($account->getChannel(), ConfigInterface::class);

        $externalData = $account->getExternalData();
        $externalDataConfig = (isset($externalData['config']) ? json_decode($externalData['config'], true) : []);
        foreach ($courierInterface->getConfigFields() as $field) {
            if (isset($config[$field->getName()])) {
                $externalDataConfig[$field->getName()] = $config[$field->getName()];
            }
        }
        $externalData['config'] = json_encode($externalDataConfig);

        try {
            $account->setExternalData($externalData);
            $this->ohAccountService->save($account);
        } catch (NotModified $e) {
            // No-op
        }
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

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }
}