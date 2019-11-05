<?php
namespace Products\Product\Listing;

use CG_Access\Service as AccessService;
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
    /** @var AccessService */
    protected $accessService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        SubscriptionService $subscriptionService,
        Sites $sites,
        SessionManager $sessionManager,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService,
        ListingTemplateService $listingTemplateService,
        AccessService $accessService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->subscriptionService = $subscriptionService;
        $this->sites = $sites;
        $this->sessionManager = $sessionManager;
        $this->featureFlagsService = $featureFlagsService;
        $this->organisationUnitService = $organisationUnitService;
        $this->listingTemplateService = $listingTemplateService;
        $this->accessService = $accessService;
    }

    public function isListingCreationAllowed(): bool
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        if ($this->accessService->hasListingsAccess()) {
            $this->logDebug('Listing creation is allowed for OU %d', ['ou' => $rootOuId], [static::LOG_CODE, 'ListingCreation', 'Allowed'], ['rootOu' => $rootOuId]);
            return true;
        }
        $this->logDebug('Listing creation is NOT allowed for OU %d', ['ou' => $rootOuId], [static::LOG_CODE, 'ListingCreation', 'NotAllowed'], ['rootOu' => $rootOuId]);
        return false;
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