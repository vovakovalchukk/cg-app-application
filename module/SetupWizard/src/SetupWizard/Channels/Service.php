<?php
namespace SetupWizard\Channels;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Integration\Type as ChannelIntegrationType;
use CG\Channel\Service as ChannelService;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Module;
use Zend\View\Model\ViewModel;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var AccountService */
    protected $accountService;
    /** @var AmazonCryptor */
    protected $amazonCryptor;
    /** @var ChannelService */
    protected $channelService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    protected $channelImgMap = [
        'amazon' => 'getImageNameFromAmazonAccount',
    ];

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        ChannelService $channelService,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setChannelService($channelService);
        $this->viewModelFactory = $viewModelFactory;
    }

    public function fetchAccountsForActiveUser()
    {
        $activeUser = $this->activeUserContainer->getActiveUser();

        $this->logDebugDump($activeUser, 'MY TEST', [], 'MY TEST');

        if (!($activeUser instanceof User)) {
            throw new NotFound();
        }

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

    public function getChannelBadgeViews(): array
    {
        $views = [];
        foreach ($this->getChannelBadgesData() as $details) {
            $badgeView = $this->viewModelFactory->newInstance($details);
            $badgeView->setTemplate('setup-wizard/channels/channel-badge.mustache');
            $views[$details['channel']] = $badgeView;
        }
        return $views;
    }

    public function getChannelBadgesData()
    {
        $badges = [];
        foreach ($this->getSalesChannelOptions() as $name => $details) {
            $channel = $details['channel'];
            $integrationType = $details['integrationType'] ?? null;
            $region = $details['region'] ?? null;
            $badges[$channel] = [
                'channel' => $channel,
                'image' => $this->getChannelImagePath($channel, $region),
                'region' => $region,
                'integrationType' => $integrationType,
                'name' => $name
            ];
        }

        return $badges;
    }

    protected function getChannelImagePath(string $channel, ?string $region): string
    {
        $img = $channel . ($region ? strtoupper($region) : '') . '.png';
        return Module::PUBLIC_FOLDER . 'img/channel-badges/' . $img;
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
