<?php
namespace Orders\Courier\Manifest;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\Account\Shared\Manifest\Filter as AccountManifestFilter;
use CG\Account\Shared\Manifest\Mapper as AccountManifestMapper;
use CG\Account\Shared\Manifest\Service as AccountManifestService;
use CG\Account\Shared\Manifest\Status as AccountManifestStatus;
use CG\Channel\CarrierProviderServiceRepository;
use CG\Channel\CarrierProviderServiceManifestInterface;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\OrganisationUnit\Service as UserOUService;
use DateTime;
use Orders\Courier\GetShippingAccountOptionsTrait;
use Orders\Courier\GetShippingAccountsTrait;

class Service
{
    const MANIFEST_SAVE_MAX_ATTEMPTS = 2;

    use GetShippingAccountsTrait {
        getShippingAccounts as traitGetShippingAccounts;
    }
    use GetShippingAccountOptionsTrait;

    /** @var AccountService */
    protected $accountService;
    /** @var UserOUService */
    protected $userOuService;
    /** @var CarrierProviderServiceRepository */
    protected $carrierProviderServiceRepo;
    /** @var AccountManifestMapper */
    protected $accountManifestMapper;
    /** @var AccountManifestService */
    protected $accountManifestService;
    /** @var OrderLabelService */
    protected $orderLabelService;

    public function __construct(
        AccountService $accountService,
        UserOUService $userOuService,
        CarrierProviderServiceRepository $carrierProviderServiceRepo,
        AccountManifestMapper $accountManifestMapper,
        AccountManifestService $accountManifestService,
        OrderLabelService $orderLabelService
    ) {
        $this->setAccountService($accountService)
            ->setUserOuService($userOuService)
            ->setCarrierProviderServiceRepo($carrierProviderServiceRepo)
            ->setAccountManifestMapper($accountManifestMapper)
            ->setAccountManifestService($accountManifestService)
            ->setOrderLabelService($orderLabelService);
    }

    public function getShippingAccounts()
    {
        $accounts = $this->traitGetShippingAccounts();
        $manifestableAccounts = new AccountCollection(Account::class, __FUNCTION__);
        foreach ($accounts as $account)
        {
            if (!$this->carrierProviderServiceRepo->isProvidedAccount($account)) {
                continue;
            }
            $provider = $this->getCarrierProviderService($account);
            if (!$provider instanceof CarrierProviderServiceManifestInterface
                || !$provider->isManifestingAllowedForAccount($account)
            ) {
                continue;
            }
            $manifestableAccounts->attach($account);
        }
        return $manifestableAccounts;
    }

    public function getShippingAccountOptions()
    {
        $shippingAccounts = $this->getShippingAccounts();
        $selectedAccountId = null;
        // If there's only one account select it
        if (count($shippingAccounts) == 1) {
            $shippingAccounts->rewind();
            $selectedAccountId = $shippingAccounts->current()->getId();
        }
        return $this->convertShippingAccountsToOptions($shippingAccounts, $selectedAccountId);
    }

    /**
     * @return array ['openOrders' => int, 'oncePerDay' => bool, 'manifestedToday' => bool]
     */
    public function getDetailsForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $latestManifestDate = $this->getLatestManifestDateForShippingAccount($account);
        $openOrders = $this->getOpenOrderCountForAccount($account, $latestManifestDate);

        return [
            'openOrders' => $openOrders,
            'oncePerDay' => $this->getCarrierProviderService($account)->isManifestingOnlyAllowedOncePerDayForAccount($account),
            'manifestedToday' => ($latestManifestDate != null && $latestManifestDate->format('Y-m-d') == date('Y-m-d')),
        ];
    }

    /**
     * @return DateTime|null
     */
    protected function getLatestManifestDateForShippingAccount(Account $account)
    {
        try {
            $filter = (new AccountManifestFilter())
                ->setLimit(1)
                ->setPage(1)
                ->setAccountId([$account->getId()])
                ->setOrderBy('created')
                ->setOrderDirection('DESC');
            $manifests = $this->accountManifestService->fetchCollectionByFilter($filter);
            $manifests->rewind();
            return new DateTime($manifests->current()->getCreated());

        } catch (NotFound $e) {
            return null;
        }
    }

    protected function getOpenOrderCountForAccount(Account $account, DateTime $latestManifestDate = null)
    {
        try {
            $filter = (new OrderLabelFilter())
                ->setLimit(1)
                ->setPage(1)
                ->setShippingAccountId([$account->getId()])
                ->setStatus(array_values(OrderLabelStatus::getPrintableStatuses()));
            if ($latestManifestDate) {
                $filter->setCreatedFrom($latestManifestDate->format(StdlibDateTime::FORMAT));
            }
            $orderLabels = $this->orderLabelService->fetchCollectionByFilter($filter);
            return $orderLabels->getTotal();

        } catch (NotFound $e) {
            return 0;
        }
    }

    protected function getHistoricManifestPeriods(
        Account $account,
        $periodValueDateLetter,
        $periodTextDateLetter = null,
        $createdFrom = null,
        $createdTo = null
    ) {
        $periodTextDateLetter = ($periodTextDateLetter ?: $periodValueDateLetter);
        $periodOptions = [];
        try {
            $knownPeriods = [];
            $accountManifests = $this->getHistoricManifests($account, $createdFrom, $createdTo);
            foreach ($accountManifests as $accountManifest) {
                $currentPeriod = date($periodValueDateLetter, strtotime($accountManifest->getCreated()));
                if (isset($knownPeriods[$currentPeriod])) {
                    continue;
                }
                $knownPeriods[$currentPeriod] = $accountManifest->getCreated();
            }
            $periodOptions = $this->getHistoricManifestPeriodOptions($knownPeriods, $periodTextDateLetter);
        } catch (NotFound $e) {
            // No-op
        }
        return $periodOptions;
    }

    protected function getHistoricManifests(Account $account, $createdFrom = null, $createdTo = null)
    {
        $filter = (new AccountManifestFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setAccountId([$account->getId()])
            ->setStatus(array_values(AccountManifestStatus::getPrintableStatuses()));
        if ($createdFrom) {
            $filter->setCreatedFrom($createdFrom);
        }
        if ($createdTo) {
            $filter->setCreatedTo($createdTo);
        }
        return $this->accountManifestService->fetchCollectionByFilter($filter);
    }

    protected function getHistoricManifestPeriodOptions(array $periods, $periodTextDateLetter, $selectSingle = true)
    {
        asort($periods, SORT_DESC);
        $selected = false;
        if ($selectSingle && count($periods) == 1) {
            $selected = true;
        }
        $periodOptions = [];
        foreach ($periods as $period => $date) {
            $periodOptions[] = [
                'value' => $period,
                'title' => date($periodTextDateLetter, strtotime($date)),
                'selected' => $selected,
            ];
        }
        return $periodOptions;
    }

    /**
     * @return \CG\Account\Shared\Manifest\Entity
     */
    public function generateManifestForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $accountManifest = $this->createAccountManifest($account);
        try {
            $this->getCarrierProviderService($account)->createManifestForAccount($account, $accountManifest);

            $accountManifest->setStatus(AccountManifestStatus::NOT_PRINTED)
                ->setCreated((new StdlibDateTime())->stdFormat());

            $this->saveAccountManifest($accountManifest);
            return $accountManifest;

        } catch (StorageException $e) {
            $this->accountManifestService->remove($accountManifest);
            throw $e;
        }
    }

    protected function createAccountManifest(Account $account)
    {
        $now = new StdlibDateTime();
        $accountManifest = $this->accountManifestMapper->fromArray([
            'organisationUnitId' => $account->getOrganisationUnitId(),
            'accountId' => $account->getId(),
            'status' => AccountManifestStatus::CREATING,
            'created' => $now->stdFormat(),
        ]);
        $hal = $this->accountManifestService->save($accountManifest);
        return $this->accountManifestMapper->fromHal($hal);
    }

    protected function saveAccountManifest(AccountManifest $accountManifest, $attempt = 1)
    {
        try {
            $this->accountManifestService->save($accountManifest);
        } catch (NotModified $e) {
            // No-op
        } catch (Conflict $e) {
            if ($attempt >= static::MANIFEST_SAVE_MAX_ATTEMPTS) {
                throw $e;
            }
            $fetchedAccountManifest = $this->accountManifestService->fetch($accountManifest->getId());
            $accountManifest->setStoredETag($fetchedAccountManifest->getStoredETag());
            $this->saveAccountManifest($accountManifest, ++$attempt);
        }
    }

    public function getHistoricManifestYearsForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        return $this->getHistoricManifestPeriods($account, 'Y');
    }

    public function getHistoricManifestMonthsForShippingAccount($accountId, $year)
    {
        $account = $this->accountService->fetch($accountId);
        $createdFrom = $year . '-01-01 00:00:00';
        $createdTo = $year . '-12-31 23:59:59';
        return $this->getHistoricManifestPeriods($account, 'm', 'M', $createdFrom, $createdTo);
    }

    public function getHistoricManifestDatesForShippingAccount($accountId, $year, $month)
    {
        $account = $this->accountService->fetch($accountId);
        $createdFrom = $year . '-' . $month . '-01 00:00:00';
        $lastDayOfMonth = date('t', strtotime($year . '-' . $month . '-01'));
        $createdTo = $year . '-' . $month . '-' . $lastDayOfMonth . ' 23:59:59';

        $accountManifests = $this->getHistoricManifests($account, $createdFrom, $createdTo);
        $periods = [];
        foreach ($accountManifests as $accountManifest) {
            $periods[$accountManifest->getId()] = $accountManifest->getCreated();
        }
        return $this->getHistoricManifestPeriodOptions($periods, 'd @ H:i', false);
    }

    public function getManifestPdfForAccountManifest($accountManifestId)
    {
        $accountManifest = $this->accountManifestService->fetch($accountManifestId);
        return base64_decode($accountManifest->getManifest());
    }

    protected function getCarrierProviderService(Account $account)
    {
        return $this->carrierProviderServiceRepo->getProviderForAccount($account);
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setUserOuService(UserOUService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    protected function setCarrierProviderServiceRepo(CarrierProviderServiceRepository $carrierProviderServiceRepo)
    {
        $this->carrierProviderServiceRepo = $carrierProviderServiceRepo;
        return $this;
    }

    protected function setAccountManifestMapper(AccountManifestMapper $accountManifestMapper)
    {
        $this->accountManifestMapper = $accountManifestMapper;
        return $this;
    }

    protected function setAccountManifestService(AccountManifestService $accountManifestService)
    {
        $this->accountManifestService = $accountManifestService;
        return $this;
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }

    // Required by GetShippingAccountsTrait
    protected function getAccountService()
    {
        return $this->accountService;
    }
    protected function getUserOuService()
    {
        return $this->userOuService;
    }
}