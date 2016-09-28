<?php
namespace SetupWizard\Messages;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Amazon\Message\AccountAddressGenerator;
use CG\Channel\Type as ChannelType;
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\User\ActiveUserInterface;

class Service
{
    const AMAZON_SETTINGS_URI = '/gp/on-board/configuration/global-seller-profile/index.html?exceptionMarketplaceID=all';

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var AccountService */
    protected $accountService;
    /** @var InvoiceSettingsService */
    protected $invoiceSettingsService;
    /** @var AccountAddressGenerator */
    protected $accountAddressGenerator;
    /** @var Cryptor */
    protected $cryptor;

    protected $accounts = [];

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        AccountService $accountService,
        InvoiceSettingsService $invoiceSettingsService,
        AccountAddressGenerator $accountAddressGenerator,
        Cryptor $cryptor
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->accountService = $accountService;
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->accountAddressGenerator = $accountAddressGenerator;
        $this->cryptor = $cryptor;
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
        $account = $this->fetchAccountById($accountId);
        $emailGenerator = $this->accountAddressGenerator;
        return $emailGenerator($account);
    }

    public function markAmazonMessagingSetupDone($accountId)
    {
        $account = $this->fetchAccountById($accountId);
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
     * @return string
     */
    public function getAmazonSettingsUrlForAccount($accountId)
    {
        $account = $this->fetchAccountById($accountId);
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        $marketplaceClass = 'CG\\Amazon\\Account\\' . ucfirst(strtolower($credentials->getRegionCode()));
        return 'https://' . constant($marketplaceClass . '::BASE_URL') . static::AMAZON_SETTINGS_URI;
    }

    /**
     * @return Account
     */
    protected function fetchAccountById($id)
    {
        if (isset($this->accounts[$id])) {
            return $this->accounts[$id];
        }
        $this->accounts[$id] = $this->accountService->fetch($id);
        return $this->accounts[$id];
    }
}