<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account as CAAccount;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Implementation\ServiceAwareInterface as AdapterImplementationServiceAwareInterface;
use CG\CourierAdapter\Provider\Credentials;

class CreationService extends CreationServiceAbstract implements AdapterImplementationServiceAwareInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;

    public function configureAccount(AccountEntity $account, array $params)
    {
        $channelName = $params['channel'];
        $adapterImplementation = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInterface = $this->adapterImplementationService->getAdapterImplementationCourierInterface($adapterImplementation);
        
        $account->setChannel($channelName)
            ->setType([ChannelType::SHIPPING])
            ->setDisplayName($adapterImplementation->getDisplayName());

        if ($courierInterface instanceof CredentialRequestInterface && !$account->getId()) {
            $this->configureAccountFromCredentialsRequest($account, $params);
            return $account;
        }
        if ($courierInterface instanceof LocalAuthInterface) {
            $this->configureAccountFromLocalAuth($account, $params, $courierInterface);
            return $account;
        }
        if ($courierInterface instanceof ThirdPartyAuthInterface) {
            $this->configureAccountFromThirdPartyAuth($account, $params, $courierInterface);
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
        CourierInterface $courierInterface
    ) {
        $account->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        foreach ($courierInterface->getCredentialsFields() as $field) {
            $credentials->set($field->getName(), ($params[$field->getName()] ?: null));
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));
    }

    protected function configureAccountFromThirdPartyAuth(
        AccountEntity $account,
        array $params,
        CourierInterface $courierInterface
    ) {
        $caAccount = $courierInterface->validateCredentials($params);
        if (!$caAccount instanceof CAAccount) {
            throw new InvalidCredentialsException('Return value of validateCredentials() was not an Account object');
        }

        $account->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        foreach ($caAccount->getCredentials() as $field => $value) {
            $credentials->set($field, $value);
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));

        if ($caAccount->getConfig()) {
            $externalData = $account->getExternalData();
            $externalData['config'] = json_encode($caAccount->getConfig());
            $account->setExternalData($externalData);
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