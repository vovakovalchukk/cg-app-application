<?php
namespace Settings\Stock;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\CGLib\Gearman\WorkerFunction\PushAllStockForAccount;
use CG\CGLib\Gearman\Workload\PushAllStockForAccount as PushAllStockForAccountWorkload;
use CG\Channel\Type as ChannelType;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use GearmanClient;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const STOCK_FETCH_LIMIT = 300;

    const LOG_CODE = 'StockSettingsService';
    const LOG_SAVE_DEFAULTS = 'Saving default stock settings for OU %d: %s';
    const LOG_STOCK_PUSH = 'Triggering stock push for %d Accounts';
    const LOG_STOCK_MANAGEMENT_OFF = 'Stock Management for Account %d turned off, will skip';
    const LOG_STOCK_MANAGEMENT_ON = 'Stock Management for Account %d turned on, will proceed';
    const LOG_STOCK_PUSH_JOB = 'Created Gearman job %s for pushing stock for Account %d';
    const LOG_SAVE_ACCOUNTS = 'Saving settings for Account(s): %s';

    /** @var AccountService */
    protected $accountService;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var GearmanClient */
    protected $gearmanClient;

    public function __construct(
        AccountService $accountService,
        ProductSettingsService $productSettingsService,
        GearmanClient $gearmanClient
    ) {
        $this->accountService = $accountService;
        $this->productSettingsService = $productSettingsService;
        $this->gearmanClient = $gearmanClient;
    }

    public function saveDefaults(
        OrganisationUnit $rootOu,
        array $ouList,
        $defaultStockMode,
        $defaultStockLevel,
        ?bool $includePurchaseOrders,
        bool $lowStockThresholdOn,
        ?int $lowStockThresholdValue
    ) {
        $this->addGlobalLogEventParam('ou', $rootOu->getId());
        $defaultsString = 'stock mode "' . $defaultStockMode . '", stock level "' . $defaultStockLevel . '"';
        $this->logDebug(static::LOG_SAVE_DEFAULTS, [$rootOu->getId(), $defaultsString], static::LOG_CODE);

        /** @var ProductSettings $productSettings */
        $productSettings = $this->productSettingsService->fetch($rootOu->getId());

        $productSettings
            ->setDefaultStockMode($defaultStockMode)
            ->setDefaultStockLevel($defaultStockLevel)
            ->setLowStockThresholdOn($lowStockThresholdOn)
            ->setLowStockThresholdValue($lowStockThresholdValue);

        if ($includePurchaseOrders !== null) {
            $productSettings->setIncludePurchaseOrdersInAvailable($includePurchaseOrders);
        }

        try {
            $this->productSettingsService->save($productSettings);
        } catch (NotModified $e) {
            // No-op
        }

        $this->triggerStockPushForAccounts(
            $this->getSalesAccountsForOU($ouList)
        );

        $this->removeGlobalLogEventParam('ou');
    }

    protected function triggerStockPushForAccounts(AccountCollection $accounts)
    {
        $this->logDebug(static::LOG_STOCK_PUSH, [count($accounts)], static::LOG_CODE);
        foreach ($accounts as $account) {
            $this->addGlobalLogEventParam('account', $account->getId());
            if (!$account->getStockManagement()) {
                $this->logDebug(static::LOG_STOCK_MANAGEMENT_OFF, [$account->getId()], static::LOG_CODE);
                $this->removeGlobalLogEventParam('account');
                continue;
            }
            $this->logDebug(static::LOG_STOCK_MANAGEMENT_ON, [$account->getId()], static::LOG_CODE);
            $workload = new PushAllStockForAccountWorkload($account);
            $jobId = $this->gearmanClient->doBackground(
                PushAllStockForAccount::FUNCTION_NAME,
                serialize($workload),
                PushAllStockForAccount::FUNCTION_NAME . $account->getId()
            );
            $this->logDebug(static::LOG_STOCK_PUSH_JOB, [$jobId, $account->getId()], static::LOG_CODE);
            $this->removeGlobalLogEventParam('account');
        }
    }

    public function getAccountListData(array $ouList)
    {
        $accounts = $this->getSalesAccountsForOU($ouList);
        return $this->formatAccountsAsListData($accounts);
    }

    protected function getSalesAccountsForOU(array $ouList)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouList)
            ->setType(ChannelType::SALES)
            ->setDeleted(false);
        try {
            return $this->accountService->fetchByFilter($filter);
        } catch (NotFound $e) {
            return new AccountCollection(Account::class, __FUNCTION__);
        }
    }

    protected function formatAccountsAsListData(AccountCollection $accounts)
    {
        $data = [];
        foreach ($accounts as $account) {
            $data[] = [
                'id' => $account->getId(),
                'channel' => $account->getChannel(),
                'channelImgUrl' => $account->getImageUrl(),
                'displayName' => $account->getDisplayName(),
                'stockMaximumEnabled' => $account->getStockMaximumEnabled(),
                'stockFixedEnabled' => $account->getStockFixedEnabled(),
            ];
        }
        return $data;
    }

    public function saveAccountsStockSettings(array $accountsSettings)
    {
        if (empty($accountsSettings)) {
            return;
        }
        $accountIds = array_keys($accountsSettings);
        $this->logDebug(static::LOG_SAVE_ACCOUNTS, [implode(',', $accountIds)], static::LOG_CODE);
        $accounts = $this->getAccountsByIds($accountIds);
        foreach ($accountsSettings as $accountId => $settings) {
            $account = $accounts->getById($accountId);
            foreach ($settings as $setting => $value) {
                $setter = 'set' . ucfirst($setting);
                $account->$setter($value);
            }
            $this->accountService->save($account);
        }
        $this->triggerStockPushForAccounts($accounts);
    }

    protected function getAccountsByIds(array $ids)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($ids);
        return $this->accountService->fetchByFilter($filter);
    }
}
