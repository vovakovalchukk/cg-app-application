<?php
namespace Products\Listing;

use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Listing\Unimported\Service as ListingService;
use CG\Listing\Unimported\Filter as ListingFilter;
use CG\Listing\Unimported\Collection as ListingCollection;
use CG\Listing\Unimported\Status as ListingStatus;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use Zend\Mvc\MvcEvent;
use CG\Channel\ListingImportFactory;
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use \GearmanClient;
use CG\Channel\Gearman\Workload\ImportListing as ImportListingWorkload;
use CG\Listing\Unimported\Status as UnimportedStatus;

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
        $listingFilter->setOrganisationUnitId($this->getActiveUser()->getOuList());
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
        try {
            $accounts = $this->getAccountService()->fetchByFilter($filter);
        } catch (NotFound $e) {
            return;
        }
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

    public function alterListingTable(ListingCollection $listingCollection, MvcEvent $event)
    {
        $listings = $listingCollection->toArray();
        $listings = $this->addAccountDetailsToListings($listings, $event);
        $listings = $this->addImagesToListings($listings, $listingCollection);
        $listings = $this->statusAlterations($listings);
        return $listings;
    }

    protected function addAccountDetailsToListings(array $listings, MvcEvent $event)
    {
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::DEFAULT_LIMIT,
            static::DEFAULT_PAGE,
            ChannelType::SALES
        );

        foreach($listings as &$listing) {
            $accountEntity = $accounts->getById($listing['accountId']);
            if ($accountEntity) {
                $listing['accountName'] = $accountEntity->getDisplayName();
            }

            $listing['accountLink'] = $event->getRouter()->assemble(
                ['account' => $listing['accountId'], 'type' => ChannelType::SALES],
                ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ROUTE_ACCOUNT]
            );
        }
        return $listings;
    }

    protected function addImagesToListings(array $listings, ListingCollection $listingCollection)
    {
        foreach ($listings as $index => $listing) {
            $listingEntity = $listingCollection->getById($listing['id']);
            if (!($listingEntity->getImage())) {
                $listings[$index]['image'] = "";
                continue;
            }

            $listings[$index]['image'] = $listingEntity->getImage()->getUrl();
        }
        return $listings;
    }

    protected function statusAlterations(array $listings)
    {
        foreach ($listings as &$listing) {
            if ($listing['status'] == ListingStatus::CANNOT_IMPORT) {
                $listing['sku'] = 'SKU(s) Not Found - Cannot Import';
            }
            $listing['statusClass'] = $listing['status'];
            $listing['status'] = str_replace('_', ' ', $listing['status']);
        }
        return $listings;
    }

    public function hideListingsById(array $listingIds)
    {
        $filter = new ListingFilter(static::DEFAULT_LIMIT, static::DEFAULT_PAGE);
        $filter->setId($listingIds); 
        $listings = $this->getListingService()->fetchCollectionByFilter($filter);
        foreach ($listings as $listing) {
            $listing->setHidden(true);
        }
        $this->getListingService()->saveCollection($listings);
    }

    public function importListingsById(array $listingIds)
    {
        $filter = new ListingFilter(static::DEFAULT_LIMIT, static::DEFAULT_PAGE);
        $filter->setId($listingIds);
        $listings = $this->getListingService()->fetchCollectionByFilter($filter);
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::DEFAULT_LIMIT,
            static::DEFAULT_PAGE,
            ChannelType::SALES
        );

        foreach ($listings as $listing) {
            if ($listing->getStatus() == ListingStatus::CANNOT_IMPORT) {
                continue;
            }
            $gearmanJob = $listing->getChannel() . 'ImportListing';
            $workload = new ImportListingWorkload($accounts->getById($listing->getAccountId()), $listing);
            $this->getGearmanClient()->doBackground($gearmanJob, serialize($workload), 'importListing' . $listing->getId());
            $listing->setStatus(UnimportedStatus::IMPORTING);
        }
        $this->getListingService()->saveCollection($listings);
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

    protected function getAccountService()
    {
        return $this->accountService;
    }

    protected function setAccountService($accountService)
    {
        $this->accountService = $accountService;
        return $this;
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