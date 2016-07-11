<?php
namespace SetupWizard\Messages;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Channel\Type as ChannelType;
use CG\Channel\Service as ChannelService;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
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
    /** @var InvoiceSettingsService */
    protected $invoiceSettingsService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        ChannelService $channelService,
        InvoiceSettingsService $invoiceSettingsService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setChannelService($channelService)
            ->setInvoiceSettingsService($invoiceSettingsService);
    }

    public function getEmailInvoiceOnDispatchToggleValue()
    {
        $invoiceSettings = $this->invoiceSettingsService->fetch($this->activeUserContainer->getActiveUser()->getOrganisationUnitId());

        if ($invoiceSettings->getAutoEmail()) {
            return true;
        }
        return false;
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

    public function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }
}