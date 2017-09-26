<?php
namespace SetupWizard\Channels;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Integration\Type as ChannelIntegrationType;
use CG\Channel\Service as ChannelService;
use CG\Channel\Type as ChannelType;
use CG\User\ActiveUserInterface;
use SetupWizard\Module;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var AccountService */
    protected $accountService;
    /** @var AmazonCryptor */
    protected $amazonCryptor;
    /** @var ChannelService */
    protected $channelService;

    protected $channelImgMap = [
        'amazon' => 'getImageNameFromAmazonAccount',
    ];

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        ChannelService $channelService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setChannelService($channelService);
    }

    public function fetchAccountsForActiveUser()
    {
        $activeUser = $this->activeUserContainer->getActiveUser();
        $ouList = $activeUser->getOuList();
        return $this->fetchAccountsForOUList($ouList);
    }

    protected function fetchAccountsForOUList(array $ouList)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouList)
            ->setDeleted(false)
            ->setType(ChannelType::SALES);
        return $this->accountService->fetchByFilter($filter);
    }

    public function getImageFromAccount(Account $account)
    {
        $externalData = $account->getExternalData();
        if (isset($externalData['imageUrl']) && !empty($externalData['imageUrl'])) {
            return $externalData['imageUrl'];
        }

        $channel = $account->getChannel();
        $img = $channel . '.png';
        if (isset($this->channelImgMap[$channel])) {
            $method = $this->channelImgMap[$channel];
            $img = $this->$method($account);
        }

        return Module::PUBLIC_FOLDER . 'img/channel-badges/' . $img;
    }

    protected function getImageNameFromAmazonAccount(Account $account)
    {
        if ($account->getChannel() != 'amazon') {
            throw new \InvalidArgumentException('Only Amazon accounts can be passed to ' . __METHOD__);
        }
        $credentials = $this->amazonCryptor->decrypt($account->getCredentials());
        $region = $credentials->getRegionCode();
        return 'amazon' . strtoupper($region) . '.png';
    }

    public function getSalesChannelOptions()
    {
        return array_merge(
            $this->channelService->getSalesChannelsByIntegrationType([ChannelIntegrationType::INTERNAL]),
            $this->channelService->getSalesChannelsByIntegrationType([ChannelIntegrationType::CLASSIC]),
            $this->channelService->getSalesChannelsByIntegrationType([ChannelIntegrationType::THIRD_PARTY]),
            $this->channelService->getSalesChannelsByIntegrationType([ChannelIntegrationType::UNSUPPORTED])
        );
    }

    public function getSalesChannelDisplayName($channel)
    {
        $salesChannelOptions = $this->getSalesChannelOptions();
        foreach ($salesChannelOptions as $displayName => $details) {
            if ($details['channel'] == $channel) {
                return $displayName;
            }
        }
        return ucfirst($channel);
    }

    public function updateAccount($id, array $data)
    {
        $account = $this->accountService->fetch($id);
        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);
            $account->$setter($value);
        }
        $this->accountService->save($account);
    }

    public function deleteAccount($id)
    {
        $account = $this->accountService->fetch($id);
        $this->accountService->delete($account);
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setAmazonCryptor(AmazonCryptor $amazonCryptor)
    {
        $this->amazonCryptor = $amazonCryptor;
        return $this;
    }

    protected function setChannelService(ChannelService $channelService)
    {
        $this->channelService = $channelService;
        return $this;
    }
}
