<?php
namespace Orders\Courier\Manifest;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Filter as AccountManifestFilter;
use CG\Account\Shared\Manifest\Mapper as AccountManifestMapper;
use CG\Account\Shared\Manifest\Service as AccountManifestService;
use CG\Account\Shared\Manifest\Status as AccountManifestStatus;
use CG\Dataplug\Carriers;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\Dataplug\Manifest\Service as DataplugManifestService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\OrganisationUnit\Service as UserOUService;
use Orders\Courier\GetShippingAccountsTrait;
use Orders\Courier\GetShippingAccountOptionsTrait;

class Service
{
    use GetShippingAccountsTrait {
        getShippingAccounts as traitGetShippingAccounts;
    }
    use GetShippingAccountOptionsTrait;

    /** @var AccountService */
    protected $accountService;
    /** @var UserOUService */
    protected $userOuService;
    /** @var CarrierService */
    protected $carrierService;
    /** @var DataplugManifestService */
    protected $dataplugManifestService;
    /** @var AccountManifestMapper */
    protected $accountManifestMapper;
    /** @var AccountManifestService */
    protected $accountManifestService;

    public function __construct(
        AccountService $accountService,
        UserOUService $userOuService,
        CarrierService $carrierService,
        DataplugManifestService $dataplugManifestService,
        AccountManifestMapper $accountManifestMapper,
        AccountManifestService $accountManifestService
    ) {
        $this->setAccountService($accountService)
            ->setUserOuService($userOuService)
            ->setCarrierService($carrierService)
            ->setDataplugManifestService($dataplugManifestService)
            ->setAccountManifestMapper($accountManifestMapper)
            ->setAccountManifestService($accountManifestService);
    }

    public function getShippingAccounts()
    {
        $accounts = $this->traitGetShippingAccounts();
        $manifestableAccounts = new AccountCollection(Account::class, __FUNCTION__);
        foreach ($accounts as $account)
        {
            $carrier = $this->carrierService->getCarrierForAccount($account);
            if (!$carrier->getAllowsManifesting()) {
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
        // If there's only one account select it, otherwise select OBA if present
        if (count($shippingAccounts) == 1) {
            $shippingAccounts->rewind();
            $selectedAccountId = $shippingAccounts->current()->getId();
        } else {
            foreach ($shippingAccounts as $shippingAccount) {
                $carrier = $this->carrierService->getCarrierForAccount($shippingAccount);
                if ($carrier->getChannelName() != Carriers::ROYAL_MAIL_OBA) {
                    continue;
                }
                $selectedAccountId = $shippingAccount->getId();
            }
        }
        return $this->convertShippingAccountsToOptions($shippingAccounts, $selectedAccountId);
    }

    /**
     * @return array ['openOrders' => int, 'oncePerDay' => bool, 'manifestedToday' => bool]
     */
    public function getDetailsForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $carrier = $this->carrierService->getCarrierForAccount($account);

        $dataplugManifests = $this->dataplugManifestService->retrieveDataplugManifestsForAccount($account);
        $openOrders = $this->dataplugManifestService->getOpenOrderCountFromRetrieveResponse($dataplugManifests);
        $latestManifestDate = $this->dataplugManifestService->getLatestManifestDateFromRetrieveResponse($dataplugManifests);

        return [
            'openOrders' => $openOrders,
            'oncePerDay' => $carrier->getManifestOncePerDay(),
            'manifestedToday' => ($latestManifestDate != null && date('Y-m-d', strtotime($latestManifestDate)) == date('Y-m-d')),
        ];
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
     * @return string \CG\Account\Shared\Manifest\Entity
     */
    public function generateManifestForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $accountManifest = $this->createAccountManifest($account);
        try {
            $this->dataplugManifestService->createManifestForAccount($account, $accountManifest);
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

    protected function setCarrierService(CarrierService $carrierService)
    {
        $this->carrierService = $carrierService;
        return $this;
    }

    protected function setDataplugManifestService(DataplugManifestService $dataplugManifestService)
    {
        $this->dataplugManifestService = $dataplugManifestService;
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