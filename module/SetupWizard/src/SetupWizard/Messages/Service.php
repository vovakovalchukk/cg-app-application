<?php
namespace SetupWizard\Messages;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Channel\Type as ChannelType;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var AccountService */
    protected $accountService;
    /** @var AmazonCryptor */
    protected $amazonCryptor;
    /** @var InvoiceSettingsService */
    protected $invoiceSettingsService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        InvoiceSettingsService $invoiceSettingsService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setInvoiceSettingsService($invoiceSettingsService);
    }

    public function fetchInvoiceSettings()
    {
        $ouId = $this->activeUserContainer->getActiveUser()->getOrganisationUnitId();
        return $this->invoiceSettingsService->fetch($ouId);
    }

    public function fetchAmazonAccountsForActiveUser()
    {
        $activeUser = $this->activeUserContainer->getActiveUser();
        $ouList = $activeUser->getOuList();
        return $this->fetchAmazonAccountsForOUList($ouList);
    }

    protected function fetchAmazonAccountsForOUList(array $ouList)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouList)
            ->setDeleted(false)
            ->setType(ChannelType::SALES)
            ->setChannel(['amazon']);
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
    public function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }
}