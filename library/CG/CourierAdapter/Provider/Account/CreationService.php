<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account as CAAccount;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Implementation\ServiceAwareInterface as AdapterImplementationServiceAwareInterface;
use CG\CourierAdapter\Provider\Credentials;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Form\Fieldset as ZendFormFieldset;

class CreationService extends CreationServiceAbstract implements AdapterImplementationServiceAwareInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;

    public function configureAccount(AccountEntity $account, array $params)
    {
        $channelName = $params['channel'];
        $adapterImplementation = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstance($adapterImplementation);
        
        $account->setChannel($channelName)
            ->setType([ChannelType::SHIPPING])
            ->setDisplayName($adapterImplementation->getDisplayName())
            ->setDisplayChannel($adapterImplementation->getDisplayName());

        if ($courierInstance instanceof CredentialRequestInterface && !$account->getId()) {
            $this->configureAccountFromCredentialsRequest($account, $params);
            return $account;
        }
        if ($courierInstance instanceof LocalAuthInterface) {
            $this->configureAccountFromLocalAuth($account, $params, $courierInstance);
            return $account;
        }
        if ($courierInstance instanceof ThirdPartyAuthInterface) {
            $this->configureAccountFromThirdPartyAuth($account, $params, $courierInstance);
            return $account;
        }
        
        return $account;
    }

    protected function configureAccountFromCredentialsRequest(AccountEntity $account, array $params)
    {
        $account->setPending(true);
        $credentials = new Credentials();
        $account->setCredentials($this->cryptor->encrypt($credentials));
    }

    protected function configureAccountFromLocalAuth(
        AccountEntity $account,
        array $params,
        CourierInterface $courierInstance
    ) {
        $account->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        foreach ($courierInstance->getCredentialsFields() as $field) {
            $credentials->set($field->getName(), ($params[$field->getName()] ?: null));
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));

        $this->addConfigFieldsToAccountExternalData($account, $courierInstance);
    }

    protected function configureAccountFromThirdPartyAuth(
        AccountEntity $account,
        array $params,
        CourierInterface $courierInstance
    ) {
        $caAccount = $courierInstance->validateCredentials($params);
        if (!$caAccount instanceof CAAccount) {
            throw new InvalidCredentialsException('Return value of validateCredentials() was not an Account object');
        }

        $account->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        foreach ($caAccount->getCredentials() as $field => $value) {
            $credentials->set($field, $value);
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));
        $account->setExternalId($caAccount->getId());

        $this->addConfigFieldsToAccountExternalData($account, $courierInstance, $caAccount->getConfig());
    }

    protected function addConfigFieldsToAccountExternalData(
        AccountEntity $account,
        CourierInterface $courierInstance,
        array $values = []
    ) {
        if (!$courierInstance instanceof ConfigInterface) {
            return;
        }
        $externalData = $account->getExternalData();
        $externalDataConfig = (isset($externalData['config']) ? json_decode($externalData['config'], true) : []);
        $this->ensureConfigFieldsInConfigArray($courierInstance->getConfigFields(), $externalDataConfig, $values);
        $externalData['config'] = json_encode($externalDataConfig);
        $account->setExternalData($externalData);
    }

    protected function ensureConfigFieldsInConfigArray(array $fields, array &$configArray, array $values = [])
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field instanceof ZendFormFieldset) {
                $this->ensureConfigFieldsInConfigArray($field->getElements(), $configArray, $values);
                continue;
            }
            if (isset($configArray[$field->getName()])) {
                continue;
            }
            $value = (isset($values[$field->getName()]) ? $values[$field->getName()] : null);
            $configArray[$field->getName()] = $value;
        }
    }

    // Required by CreationServiceAbstract but will be changed by configureAccount()
    public function getChannelName()
    {
        return '';
    }

    // Required by CreationServiceAbstract but will be changed by configureAccount()
    public function getDisplayName(array $params)
    {
        return '';
    }

    // For AdapterImplementationServiceAwareInterface
    public function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }
}