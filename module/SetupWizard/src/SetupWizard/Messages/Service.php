<?php
namespace SetupWizard\Messages;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Channel\Type as ChannelType;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\User\ActiveUserInterface;
use SetupWizard\Channels\Service as ChannelService;
use SetupWizard\Module;
use CG_UI\View\Prototyper\ViewModelFactory;

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
    /** @var InvoiceSettingsService */
    protected $invoiceSettingsService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        ChannelService $channelService,
        InvoiceSettingsService $invoiceSettingsService,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setChannelService($channelService)
            ->setInvoiceSettingsService($invoiceSettingsService)
            ->setViewModelFactory($viewModelFactory);
    }

    public function fetchInvoiceSettings()
    {
        $ouId = $this->activeUserContainer->getActiveUser()->getOrganisationUnitId();
        return $this->invoiceSettingsService->fetch($ouId);
    }

    public function getAccountBadgeSection($account)
    {
        //$setupFlag = $account->isMessagingSetup();

        $badgeSection = $this->viewModelFactory->newInstance([
            'setupFlag' => true,
        ]);
        $badgeSection->addChild($this->getAccountBadge($account), 'badge');
        $badgeSection->setTemplate('setup-wizard/messages/accountBadgeSection');

        return $badgeSection;
    }
    
    protected function getAccountBadge($account)
    {
        $img = $this->channelService->getImageFromAccount($account);
        $badgeView = $this->viewModelFactory->newInstance([
            'image' => $img,
            'id' => $account->getId(),
            'name' => $account->getDisplayName(),
        ]);
        $badgeView->setTemplate('setup-wizard/channels/account-badge.mustache');
        return $badgeView;
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

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAmazonCryptor(AmazonCryptor $amazonCryptor)
    {
        $this->amazonCryptor = $amazonCryptor;
        return $this;
    }

    /**
     * @return self
     */
    protected function setChannelService(ChannelService $channelService)
    {
        $this->channelService = $channelService;
        return $this;
    }

    /**
     * @return self
     */
    public function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }
}