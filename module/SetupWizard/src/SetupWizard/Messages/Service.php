<?php
namespace SetupWizard\Messages;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor as AmazonCryptor;
use CG\Amazon\Message\AccountAddressGenerator;
use CG\Channel\Type as ChannelType;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Http\Exception\Exception3xx\NotModified;
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
    /** @var AccountAddressGenerator */
    protected $accountAddressGenerator;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        AmazonCryptor $amazonCryptor,
        InvoiceSettingsService $invoiceSettingsService,
        AccountAddressGenerator $accountAddressGenerator
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setAccountService($accountService)
            ->setAmazonCryptor($amazonCryptor)
            ->setInvoiceSettingsService($invoiceSettingsService)
            ->setAccountAddressGenerator($accountAddressGenerator);
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

    public function getEmailForAmazonAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $emailGenerator = $this->accountAddressGenerator;
        return $emailGenerator($account);
    }

    public function markAmazonMessagingSetupDone($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $externalData = $account->getExternalData();
        $externalData['messagingSetUp'] = true;
        $account->setExternalData($externalData);

        try {
            $this->accountService->save($account);
        } catch (NotModified $e) {
            // No-op
        }
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
    protected function setInvoiceSettingsService(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setAccountAddressGenerator(AccountAddressGenerator $accountAddressGenerator)
    {
        $this->accountAddressGenerator = $accountAddressGenerator;
        return $this;
    }
}