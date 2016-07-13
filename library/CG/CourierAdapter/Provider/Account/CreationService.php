<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use CG\CourierAdapter\Provider\Adapter\ServiceAwareInterface as AdapterServiceAwareInterface;
use CG\CourierAdapter\Provider\Credentials;

class CreationService extends CreationServiceAbstract implements AdapterServiceAwareInterface
{
    /** @var AdapterService */
    protected $adapterService;

    public function configureAccount(AccountEntity $account, array $params)
    {
        $channelName = $params['channel'];
        $adapter = $this->adapterService->getAdapterByChannelName($channelName);
        $courierInterface = $this->adapterService->getAdapterCourierInterface($adapter);
        
        $account->setChannel($channelName)
            ->setType([ChannelType::SHIPPING])
            ->setDisplayName($adapter->getDisplayName());

        if ($courierInterface instanceof CredentialRequestInterface && !$account->getId()) {
            $this->configureAccountFromCredentialsRequest($account, $params);
            return $account;
        }
        if ($courierInterface instanceof LocalAuthInterface) {
            $this->configureAccountFromLocalAuth($account, $params, $courierInterface);
            return $account;
        }
        if ($courierInterface instanceof ThirdPartyAuthInterface) {
            // TODO
        }
        
        return $account;
    }

    protected function configureAccountFromCredentialsRequest(AccountEntity $account, array $params)
    {
        $account->setPending(true);
        $credentials = new Credentials();
        $account->setCredentials($this->cryptor->encrypt($credentials));
    }

    protected function configureAccountFromLocalAuth(AccountEntity $account, array $params, CourierInterface $courierInterface)
    {
        $account->setPending(false);
        $credentials = ($account->getCredentials() ? $this->cryptor->decrypt($account->getCredentials()) : new Credentials());
        foreach ($courierInterface->getCredentialsFields() as $field) {
            $credentials->set($field->getName(), ($params[$field->getName()] ?: null));
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));
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

    // For AdapterServiceAwareInterface
    public function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }
}