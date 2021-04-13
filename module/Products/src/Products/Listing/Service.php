<?php
namespace Products\Listing;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Gearman\Generator\UnimportedListing\Import as UnimportedListingImportGenerator;
use CG\Channel\Listing\Download\Service as ListingDownloadService;
use CG\Channel\Listing\Import\PermissionService as ListingImportPermissionService;
use CG\Channel\ListingImportFactory;
use CG\Channel\Type as ChannelType;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Listing\Unimported\Collection as ListingCollection;
use CG\Listing\Unimported\Filter as ListingFilter;
use CG\Listing\Unimported\Gearman\Workload\ImportListingsByFilter as ImportListingsByFilterWorkload;
use CG\Listing\Unimported\Marketplace\Client\Service as MarketplaceService;
use CG\Listing\Unimported\Service as ListingService;
use CG\Listing\Unimported\Status as ListingStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\UserPreference\Shared\Entity as UserPreference;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;
use GearmanClient;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LISTING_FILTER_BAR_STATE_KEY = 'listing-filter-bar-state';
    const ACTIVE = 1;
    const DEFAULT_LIMIT = 'all';
    const DEFAULT_PAGE = 1;
    const DEFAULT_TYPE = 'sales';
    const EVENT_LISTINGS_IMPORTED = 'Listings Imported';

    const REFRESH_STATUS_NOT_STARTED = 'Not started';
    const REFRESH_STATUS_IN_PROGRESS = 'In progress';
    const REFRESH_STATUS_IN_COMPLETED = 'Completed';

    const LOG_CODE = 'ProductsListingService';
    const LOG_IMPORT_ALL_FILTERED = 'Creating job to import all unimported listings that match the filters:';

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var UserPreferenceService */
    protected $userPreferenceService;
    /** @var UserPreference */
    protected $activeUserPreference;
    /** @var ListingService */
    protected $listingService;
    /** @var ListingImportFactory */
    protected $listingImportFactory;
    /** @var AccountService */
    protected $accountService;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var IntercomEventService */
    protected $intercomEventService;
    /** @var DateFormatHelper */
    protected $dateFormatHelper;
    /** @var UnimportedListingImportGenerator */
    protected $unimportedListingImportGenerator;
    /** @var ListingDownloadService */
    protected $listingDownloadService;
    /** @var MarketplaceService */
    protected $marketplaceService;
    /** @var ListingImportPermissionService */
    protected $listingImportPermissionService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService,
        ListingService $listingService,
        ListingImportFactory $listingImportFactory,
        AccountService $accountService,
        GearmanClient $gearmanClient,
        IntercomEventService $intercomEventService,
        DateFormatHelper $dateFormatHelper,
        UnimportedListingImportGenerator $unimportedListingImportGenerator,
        ListingDownloadService $listingDownloadService,
        MarketplaceService $marketplaceService,
        ListingImportPermissionService $listingImportPermissionService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->userPreferenceService = $userPreferenceService;
        $this->listingService = $listingService;
        $this->listingImportFactory = $listingImportFactory;
        $this->accountService = $accountService;
        $this->gearmanClient = $gearmanClient;
        $this->intercomEventService = $intercomEventService;
        $this->dateFormatHelper = $dateFormatHelper;
        $this->unimportedListingImportGenerator = $unimportedListingImportGenerator;
        $this->listingDownloadService = $listingDownloadService;
        $this->marketplaceService = $marketplaceService;
        $this->listingImportPermissionService = $listingImportPermissionService;
    }

    public function fetchListings(ListingFilter $listingFilter)
    {
        $listingFilter->setOrganisationUnitId($this->getActiveUser()->getOuList());
        return $this->listingService->fetchCollectionByFilter($listingFilter);
    }

    public function refresh(array $accountIds = [])
    {
        if (!$this->listingImportBlacklisted()) {
            return;
        }
        $filter = (new AccountFilter(static::DEFAULT_LIMIT, static::DEFAULT_PAGE))
            ->setActive(static::ACTIVE)
            ->setType(static::DEFAULT_TYPE)
            ->setRootOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);

        if (!empty($accountIds)) {
            $filter->setId($accountIds);
        }

        try {
            $accounts = $this->accountService->fetchByFilter($filter);
        } catch (NotFound $e) {
            return;
        }

        /** @var Account $account */
        foreach ($accounts as $account) {
            $importer = $this->listingImportFactory->createListingImport($account);
            $importer($account);
        }
    }

    public function getRefreshDetails(array $accountIds = [])
    {
        $filter = (new AccountFilter(static::DEFAULT_LIMIT, static::DEFAULT_PAGE))
            ->setActive(static::ACTIVE)
            ->setType(static::DEFAULT_TYPE)
            ->setRootOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);

        if (!empty($accountIds)) {
            $filter->setId($accountIds);
        }

        try {
            $accounts = $this->accountService->fetchByFilter($filter);
        } catch (NotFound $exception) {
            return [];
        }

        $refreshDetails = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            $listingDownload = $account->getListingDownload();
            $refreshDetails[$account->getId()] = [
                'channel' => $account->getChannel(),
                'name' => $account->getDisplayName(),
                'status' => static::REFRESH_STATUS_NOT_STARTED,
                'lastCompleted' => null,
                'refreshAllowed' => $this->listingDownloadService->isRefreshAllowed($account),
            ];

            if ($lastCompletedDate = $listingDownload->getLastCompletedDate()) {
                $refreshDetails[$account->getId()]['lastCompleted'] = ($this->dateFormatHelper)($lastCompletedDate);
            }

            if (is_null($listingDownload->getId())) {
                continue;
            }

            if ($listingDownload->getProcessed() >= $listingDownload->getTotal()) {
                $refreshDetails[$account->getId()]['status'] = static::REFRESH_STATUS_IN_COMPLETED;
                continue;
            }

            $refreshDetails[$account->getId()]['status'] = static::REFRESH_STATUS_IN_PROGRESS;
        }
        return $refreshDetails;
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
        $accounts = $this->accountService->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::DEFAULT_LIMIT,
            static::DEFAULT_PAGE,
            ChannelType::SALES
        );

        foreach($listings as &$listing) {
            $listing['accountLink'] = $event->getRouter()->assemble(
                ['account' => $listing['accountId'], 'type' => ChannelType::SALES],
                ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ROUTE_ACCOUNT]
            );

            $accountEntity = $accounts->getById($listing['accountId']);
            if (!$accountEntity) {
                continue;
            }

            $listing['accountName'] = $accountEntity->getDisplayName();
            $listing['channelImgUrl'] = $accountEntity->getImageUrl();

            if (isset($listing['marketplace'])) {
                $listing['marketplace'] = $this->marketplaceService->mapMarketplaceIdToName($accountEntity, $listing['marketplace']);
            }
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
            $listings[$index]['createdDate'] = $dateFormatter($listings[$index]['createdDate']);
        }
        return $listings;
    }

    public function hideListingsById(array $listingIds)
    {
        $this->listingService->patchCollection('id', $listingIds, ['hidden' => true]);
    }

    public function importListingsById(array $listingIds)
    {
        $filter = new ListingFilter(static::DEFAULT_LIMIT, static::DEFAULT_PAGE);
        $filter->setId($listingIds);
        $listings = $this->listingService->fetchCollectionByFilter($filter);

        $this->listingService->importListingsCollection($listings);
        $this->notifyOfImport();
    }

    public function importListingsByFilter(ListingFilter $listingFilter)
    {
        $this->addGlobalLogEventParam('rootOu', $this->activeUserContainer->getActiveUserRootOrganisationUnitId());

        // Ensure we only get listings for this user's OUs
        $listingFilter->setOrganisationUnitId($this->getActiveUser()->getOuList());
        $this->logDebugDump(array_filter($listingFilter->toArray()), static::LOG_IMPORT_ALL_FILTERED, [], [static::LOG_CODE, 'ImportAllFiltered']);

        // This can potentially take a long time, do it in the background
        $workload = new ImportListingsByFilterWorkload($listingFilter);
        $this->gearmanClient->doBackground(ImportListingsByFilterWorkload::FUNCTION_NAME, serialize($workload));

        $this->notifyOfImport();
        return true;
    }

    protected function notifyOfImport()
    {
        $event = new IntercomEvent(static::EVENT_LISTINGS_IMPORTED, $this->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }

    public function listingImportBlacklisted(): bool
    {
        return $this->listingImportPermissionService->listingImportBlacklisted(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        );
    }

    /**
     * @return UserPreference
     */
    protected function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->userPreferenceService->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }
}
