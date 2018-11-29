<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account as CAAccount;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\LocalAuth\CredentialsChangedInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Implementation\ServiceAwareInterface as AdapterImplementationServiceAwareInterface;
use CG\CourierAdapter\Provider\Credentials;
use CG\Stdlib\Exception\Runtime\ValidationException;

class CreationService extends CreationServiceAbstract implements AdapterImplementationServiceAwareInterface
{
    use PrepareAdapterImplementationFieldsTrait;

    const REQUEST_CREDENTIALS_SKIPPED_FIELD = '_rcs';
    const REQUEST_CREDENTIALS_FIELD = '_rc';

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

        if ($courierInstance instanceof CredentialRequestInterface 
            && !$account->getId()
            && isset($params[static::REQUEST_CREDENTIALS_FIELD])
        ) {
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
        $account->setActive(true)
            ->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        $existingCredentials = clone $credentials;
        $credentialsForm = $courierInstance->getCredentialsForm();
        $this->prepareAdapterImplementationFormForSubmission($credentialsForm, $params);
        if (!$credentialsForm->isValid()) {
            throw new ValidationException('There were problems submitting the form. Please review it and try again');
        }
        $credentials->setData($credentialsForm->getData());

        $account->setCredentials($this->cryptor->encrypt($credentials));

        $this->addConfigFieldsToAccountExternalData($account, $courierInstance);

        if ($courierInstance instanceof CredentialsChangedInterface) {
            $courierInstance->credentialsChanged($account->toArray(), $existingCredentials->toArray(), $credentials->toArray());
        }
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

        $account->setActive(true)
            ->setPending(false);
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
        if (isset($externalData['config']) && empty($values)) {
            return;
        }

        $externalDataConfig = (isset($externalData['config']) ? json_decode($externalData['config'], true) : []);
        $allValues = array_merge($externalDataConfig, $values);
        $form = $courierInstance->getConfigForm();
        $this->prepareAdapterImplementationFormForSubmission($form, $allValues);
        // We can't call getData() until isValid() has been called, even if we don't care if its valid or not
        $form->isValid();

        $externalData['config'] = json_encode($form->getData());
        $account->setExternalData($externalData);
    }

    protected function afterAccountSave(AccountEntity $account)
    {
        if ($account->getExternalId()) {
            return $account;
        }
        // If we have no externalId (e.g. when using LocalAuth) we need to set it to something so
        // the adapter code can make use of it
        $account->setExternalId($account->getId());
        return $account;
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