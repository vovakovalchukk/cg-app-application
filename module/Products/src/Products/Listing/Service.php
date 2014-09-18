<?php
namespace Products\Listing;

use CG\User\ActiveUserInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Listing\Unimported\Service as ListingService;
use CG\Listing\Unimported\Filter as ListingFilter;
use CG\Channel\ListingImportFactory;
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use \GearmanClient;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LISTING_FILTER_BAR_STATE_KEY = 'listing-filter-bar-state';
    const ACTIVE = 1;
    const DEFAULT_LIMIT = 'all';
    const DEFAULT_PAGE = 1;
    const DEFAULT_TYPE = 'sales';
    const ONE_SECOND_DELAY = 1;

    protected $activeUserContainer;
    protected $userPreferenceService;
    protected $listingService;
    protected $listingImportFactory;
    protected $accountService;
    protected $gearmanClient;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService,
        ListingService $listingService,
        ListingImportFactory $listingImportFactory,
        AccountService $accountService,
        GearmanClient $gearmanClient
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService)
            ->setListingService($listingService)
            ->setListingImportFactory($listingImportFactory)
            ->setAccountService($accountService)
            ->setGearmanClient($gearmanClient);
    }

    public function fetchListings(ListingFilter $listingFilter)
    {
        $listingFilter->setLimit(static::DEFAULT_LIMIT)
            ->setPage(static::DEFAULT_PAGE)
            ->setOrganisationUnitId($this->getActiveUser()->getOuList());
        return $this->getListingService()->fetchCollectionByFilter($listingFilter);
    }

    public function refresh()
    {
        $filter = new AccountFilter();
        $filter->setActive(static::ACTIVE)
            ->setLimit(static::DEFAULT_LIMIT)
            ->setPage(static::DEFAULT_PAGE)
            ->setType(static::DEFAULT_TYPE)
            ->setOus($this->getActiveUserContainer()->getActiveUser()->getOuList());
        $accounts = $this->getAccountService()->fetchByFilter($filter);
        $gearmanJobs = [];
        foreach ($accounts as $account) {
            $importer = $this->getListingImportFactory()->createListingImport($account);
            $gearmanJobs[] = $importer($account);
        }
        do {
            sleep(static::ONE_SECOND_DELAY);
        } while($this->checkGearmanJobStatus($gearmanJobs));
    }

    protected function checkGearmanJobStatus(array $gearmanJobs)
    {
        foreach ($gearmanJobs as $gearmanJob) {
            if ($this->getGearmanClient()->jobStatus($gearmanJob)[0]) {
                return true;
            }
        }
        return false;
    }

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::LISTING_FILTER_BAR_STATE_KEY]) ? $preference[static::LISTING_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->getUserPreferenceService()->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    protected function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    protected function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    protected function getUserPreferenceService()
    {
        return $this->userPreferenceService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function setListingService(ListingService $listingService)
    {
        $this->listingService = $listingService;
        return $this;
    }

    protected function getListingService()
    {
        return $this->listingService;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function getAccountService()
    {
        return $this->accountService;
    }

    protected function setGearmanClient(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
        return $this;
    }

    protected function getGearmanClient()
    {
        return $this->gearmanClient;
    }

    protected function setListingImportFactory(ListingImportFactory $listingImportFactory)
    {
        $this->listingImportFactory = $listingImportFactory;
        return $this;
    }

    protected function getListingImportFactory()
    {
        return $this->listingImportFactory;
    }
}