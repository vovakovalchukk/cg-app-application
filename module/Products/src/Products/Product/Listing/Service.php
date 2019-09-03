<?php
namespace Products\Product\Listing;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Subscription\Service as SubscriptionService;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Listing\Client\Service as ListingService;
use CG\Listing\Template\Collection as ListingTemplateCollection;
use CG\Listing\Template\Filter as ListingTemplateFilter;
use CG\Listing\Template\Service as ListingTemplateService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Sites;
use CG\User\ActiveUserInterface;
use Zend\Session\SessionManager;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'ProductListingService';
    const CACHE_TTL = 86400; // 24 hours

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SubscriptionService */
    protected $subscriptionService;
    /** @var Sites */
    protected $sites;
    /** @var SessionManager */
    protected $sessionManager;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var ListingTemplateService */
    protected $listingTemplateService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        SubscriptionService $subscriptionService,
        Sites $sites,
        SessionManager $sessionManager,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService,
        ListingTemplateService $listingTemplateService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->subscriptionService = $subscriptionService;
        $this->sites = $sites;
        $this->sessionManager = $sessionManager;
        $this->featureFlagsService = $featureFlagsService;
        $this->organisationUnitService = $organisationUnitService;
        $this->listingTemplateService = $listingTemplateService;
    }

    public function isListingCreationAllowed(): bool
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $cachedStatus = $this->getCachedListingCreationAllowed();
        if ($cachedStatus !== null) {
            $this->logDebug('Got Listing Creation Allowed status for OU %d from cache: %s', ['ou' => $rootOuId, $cachedStatus ? 'allowed' : 'not allowed'], [static::LOG_CODE, 'ListingCreation', 'Cached'], ['rootOu' => $rootOuId]);
            return $cachedStatus;
        }

        try {
            $this->subscriptionService->getCurrentPackageForOuId($rootOuId, Licence::TYPE_LISTING);
            $this->setCachedListingCreationAllowed(true);
            $this->logDebug('Listing creation is allowed for OU %d', ['ou' => $rootOuId], [static::LOG_CODE, 'ListingCreation', 'Allowed'], ['rootOu' => $rootOuId]);
            return true;
        } catch (NotFound $e) {
            $this->logDebug('Listing creation is NOT allowed for OU %d', ['ou' => $rootOuId], [static::LOG_CODE, 'ListingCreation', 'NotAllowed'], ['rootOu' => $rootOuId]);
            // Don't cache when false so that when users add Listings we don't have to invalidate any cache
            return false;
        }
    }

    protected function getCachedListingCreationAllowed(): ?bool
    {
        $session = $this->sessionManager->getStorage();
        if (!isset($session['ListingCreation'], $session['ListingCreation']['allowed'], $session['ListingCreation']['timestamp']) ||
            $session['ListingCreation']['timestamp'] + static::CACHE_TTL < time()) {
            return null;
        }
        return $session['ListingCreation']['allowed'];
    }

    protected function setCachedListingCreationAllowed(bool $allowed)
    {
        $session = $this->sessionManager->getStorage();
        if (!isset($session['ListingCreation'])) {
            $session['ListingCreation'] = [];
        }
        $session['ListingCreation']['allowed'] = $allowed;
        $session['ListingCreation']['timestamp'] = time();
        return $this;
    }

    public function getManagePackageUrl(): string
    {
        return $this->sites->host('admin') . '/billing/package';
    }

    public function fetchListingTemplates(): ?ListingTemplateCollection
    {
        try {
            $filter = (new ListingTemplateFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);
            return $this->listingTemplateService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return null;
        }
    }
}