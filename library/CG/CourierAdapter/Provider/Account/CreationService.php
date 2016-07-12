<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
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

        if ($courierInterface instanceof ThirdPartyAuthInterface) {
            // TODO
        }
        if ($courierInterface instanceof LocalAuthInterface) {
            // TODO
        }
        if ($courierInterface instanceof CredentialRequestInterface && !$account->getId()) {
            $account->setPending(true);
            $credentials = new Credentials();
            $account->setCredentials($this->cryptor->encrypt($credentials));
        }
        
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

    // For AdapterServiceAwareInterface
    public function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }
}