<?php
namespace Products\Listing;

use CG\Account\Client\Service as AccountService;
use CG\Channel\Type as ChannelType;
use CG\User\ActiveUserInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\Listing\Unimported\Service as ListingService;
use CG\Listing\Unimported\Filter as ListingFilter;
use CG\Listing\Unimported\Collection as ListingCollection;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LISTING_FILTER_BAR_STATE_KEY = 'listing-filter-bar-state';
    const LIMIT = 'all';
    const PAGE = 1;

    protected $activeUserContainer;
    protected $userPreferenceService;
    protected $listingService;
    protected $accountService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService,
        ListingService $listingService,
        AccountService $accountService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService)
            ->setListingService($listingService)
            ->setAccountService($accountService);
    }

    public function fetchListings(ListingFilter $listingFilter)
    {
        $listingFilter->setLimit(static::LIMIT)
            ->setPage(static::PAGE)
            ->setOrganisationUnitId($this->getActiveUser()->getOuList());
        return $this->getListingService()->fetchCollectionByFilter($listingFilter);
    }

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::LISTING_FILTER_BAR_STATE_KEY]) ? $preference[static::LISTING_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function getListingList()
    {

    }

    public function alterListingTable(ListingCollection $listingCollection, MvcEvent $event)
    {
        $listings = $listingCollection->toArray();
        $listings = $this->addAccountDetailsToListings($listings, $event);

        return $listings;
    }

    public function addAccountDetailsToListings(array $listings, MvcEvent $event)
    {
        $accounts = $this->getAccountService()->fetchByOUAndStatus(
            $this->getActiveUser()->getOuList(),
            null,
            null,
            static::LIMIT,
            static::PAGE,
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
}