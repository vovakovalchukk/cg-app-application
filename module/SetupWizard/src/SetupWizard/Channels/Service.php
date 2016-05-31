<?php
namespace SetupWizard\Channels;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Channel\Service as ChannelService;
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
        return $this->channelService->getChannels(ChannelType::SALES);
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