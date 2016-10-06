<?php
namespace Products\Listing;

use CG\Channel\Gearman\Generator\UnimportedListing\Import as UnimportedListingImportGenerator;
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
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;
use CG\Stdlib\DateTime as StdlibDateTime;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LISTING_FILTER_BAR_STATE_KEY = 'listing-filter-bar-state';
    const ACTIVE = 1;
    const DEFAULT_LIMIT = 'all';
    const DEFAULT_PAGE = 1;
    const DEFAULT_TYPE = 'sales';
    const ONE_SECOND_DELAY = 1;
    const EVENT_LISTINGS_IMPORTED = 'Listings Imported';
    const REFRESH_TIMEOUT = 300;
    const BATCH_SIZE = 300;

    const LOG_CODE = 'ProductsListingService';
    const LOG_IMPORT_ALL_FILTERED = 'Importing all unimported listings that match the filters:';
    const LOG_IMPORT_ALL_BATCH = 'Importing page %d, limit %d of unimported listings that match the filters';
    const LOG_IMPORT_ALL_NO_PGNTN = 'Filters include status which has been changed on processed listings so result set will change, not paginating';

    protected $activeUserContainer;
    protected $userPreferenceService;
    protected $listingService;
    protected $listingImportFactory;
    protected $accountService;
    protected $gearmanClient;
    protected $intercomEventService;
    protected $dateFormatHelper;
    /** @var UnimportedListingImportGenerator */
    protected $unimportedListingImportGenerator;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService,
        ListingService $listingService,
        ListingImportFactory $listingImportFactory,
        AccountService $accountService,
        GearmanClient $gearmanClient,
        IntercomEventService $intercomEventService,
        DateFormatHelper $dateFormatHelper,
        UnimportedListingImportGenerator $unimportedListingImportGenerator
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService)
            ->setListingService($listingService)
            ->setListingImportFactory($listingImportFactory)
            ->setAccountService($accountService)
            ->setGearmanClient($gearmanClient)
            ->setIntercomEventService($intercomEventService)
            ->setDateFormatHelper($dateFormatHelper)
            ->setUnimportedListingImportGenerator($unimportedListingImportGenerator);
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
        $seconds = 0;
        do {
            sleep(static::ONE_SECOND_DELAY);
        } while($this->checkGearmanJobStatus($gearmanJobs) && (++$seconds <= static::REFRESH_TIMEOUT));
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
        $listings = $this->getListingsArrayWithFormattedDates($listings);
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
                $listing['channelImgUrl'] = $accountEntity->getImageUrl();
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
            if ($listing['status'] == ListingStatus::CANNOT_IMPORT_SKU) {
                $listing['sku'] = 'SKU(s) Not Found - Cannot Import';
            }
            if ($listing['status'] == ListingStatus::UNEXPECTED_CHARS_IN_SKU) {
                $listing['sku'] = 'Unexpected Characters in SKU(s) - Cannot Import';
            }
            $listing['statusClass'] = $listing['status'];
            $listing['status'] = str_replace('_', ' ', $listing['status']);
        }
        return $listings;
    }

    protected function getListingsArrayWithFormattedDates(array $listings)
    {
        $dateFormatter = $this->dateFormatHelper;
        foreach ($listings as $index => $listing) {
            // Keep the dates in Y-m-d H:i:s, the Mustache template will change them to a human-friendly format
            $listings[$index]['createdDate'] = $dateFormatter($listings[$index]['createdDate'], StdlibDateTime::FORMAT);
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

        $this->importListingsCollection($listings);
        $this->notifyOfImport();
    }

    protected function importListingsCollection($listings)
    {
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::DEFAULT_LIMIT,
            static::DEFAULT_PAGE,
            ChannelType::SALES
        );

        foreach ($listings as $listing) {
            if (!ListingStatus::canImport($listing->getStatus())) {
                continue;
            }
            $account = $accounts->getById($listing->getAccountId());
            $this->unimportedListingImportGenerator->__invoke($listing, $account);
            $listing->setStatus(UnimportedStatus::IMPORTING);
        }
        $this->getListingService()->saveCollection($listings);
    }

    public function importListingsByFilter(ListingFilter $listingFilter)
    {
        $this->addGlobalLogEventParam('rootOu', $this->activeUserContainer->getActiveUserRootOrganisationUnitId());
        $this->logDebugDump(array_filter($listingFilter->toArray()), static::LOG_IMPORT_ALL_FILTERED, [], [static::LOG_CODE, 'ImportAllFiltered']);
        $listingFilter->setPage(static::DEFAULT_PAGE)->setLimit(static::BATCH_SIZE);

        try {
            $this->importListingsByFilterBatch($listingFilter);
            $this->notifyOfImport();
            $success = true;

        } catch (NotFound $e) {
            $success = false;
        }

        $this->removeGlobalLogEventParam('rootOu');
        return $success;
    }

    protected function importListingsByFilterBatch(ListingFilter $listingFilter)
    {
        $this->logDebug(static::LOG_IMPORT_ALL_BATCH, [$listingFilter->getPage(), $listingFilter->getLimit()], [static::LOG_CODE, 'ImportAllBatch']);
        $listings = $this->fetchListings($listingFilter);
        $this->importListingsCollection($listings);

        // Any more to fetch?
        if ($listings->getTotal() <= ($listingFilter->getPage() * $listingFilter->getLimit())) {
            return;
        }

        // As the listings are processed their status is changed to 'importing'. If the user filtered on status then the
        // result set will now be different (unless the user included 'importing' in the filter)
        // So: only paginate if there's no status filters or they include 'importing'
        $statusFiltersKeyed = array_flip($listingFilter->getStatus());
        if (empty($statusFiltersKeyed) || isset($statusFiltersKeyed[ListingStatus::IMPORTING])) {
            $listingFilter->setPage($listingFilter->getPage() + 1);
        } else {
            $this->logDebug(static::LOG_IMPORT_ALL_NO_PGNTN, [], [static::LOG_CODE, 'ImportAllNoPgntn']);
        }
        $this->importListingsByFilterBatch($listingFilter);
    }

    protected function notifyOfImport()
    {
        $event = new IntercomEvent(static::EVENT_LISTINGS_IMPORTED, $this->getActiveUser()->getId());
        $this->getIntercomEventService()->save($event);
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

    protected function getIntercomEventService()
    {
        return $this->intercomEventService;
    }

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }

    protected function setDateFormatHelper(DateFormatHelper $dateFormatHelper)
    {
        $this->dateFormatHelper = $dateFormatHelper;
        return $this;
    }

    protected function setUnimportedListingImportGenerator(UnimportedListingImportGenerator $unimportedListingImportGenerator)
    {
        $this->unimportedListingImportGenerator = $unimportedListingImportGenerator;
        return $this;
    }
}
