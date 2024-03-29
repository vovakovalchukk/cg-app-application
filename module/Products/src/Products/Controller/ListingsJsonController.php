<?php
namespace Products\Controller;

use CG\Channel\Listing\CreationService;
use CG\Channel\Listing\CreationService\Status as CreationStatus;
use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Listing\Unimported\Collection as UnimportedListingCollection;
use CG\Listing\Unimported\Filter\Mapper as FilterMapper;
use CG\Listing\Unimported\Mapper as ListingMapper;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\PageLimit;
use CG\User\ActiveUserInterface;
use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Filter\Service as FilterService;
use Products\Listing\Service as ListingService;
use Zend\Mvc\Controller\AbstractActionController;

class ListingsJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_AJAX = 'AJAX';
    const ROUTE_HIDE = 'HIDE';
    const ROUTE_REFRESH = 'refresh';
    const ROUTE_REFRESH_DETAILS = 'refreshDetails';
    const ROUTE_IMPORT = 'import';
    const ROUTE_IMPORT_ALL_FILTERED = 'import all filtered';
    const ROUTE_CREATE = 'create';
    const CHANNEL_WALMART = 'walmart';

    /** @var ListingService */
    protected $listingService;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var FilterMapper */
    protected $filterMapper;
    /** @var ListingMapper */
    protected $listingMapper;
    /** @var FilterService */
    protected $filterService;
    /** @var CreationService */
    protected $creationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var FeatureFlagService */
    protected $featureFlagService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    public function __construct(
        ListingService $listingService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        ListingMapper $listingMapper,
        FilterService $filterService,
        CreationService $creationService,
        ActiveUserInterface $activeUserContainer,
        FeatureFlagService $featureFlagService,
        OrganisationUnitService $organisationUnitService,
        AccessUsageExceededService $accessUsageExceededService
    ) {
        $this->listingService = $listingService;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->filterMapper = $filterMapper;
        $this->listingMapper = $listingMapper;
        $this->filterService = $filterService;
        $this->creationService = $creationService;
        $this->activeUserContainer = $activeUserContainer;
        $this->featureFlagService = $featureFlagService;
        $this->organisationUnitService = $organisationUnitService;
        $this->accessUsageExceededService = $accessUsageExceededService;
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getDefaultJsonData()
    {
        return new \ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
            ]
        );
    }

    public function ajaxAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();

        try {
            $requestFilter = $this->params()->fromPost('filter', []);
            $requestFilter = $this->ensureHiddenFilterApplied($requestFilter);

            $requestFilter = $this->filterMapper->fromArray($requestFilter)
                ->setPage($pageLimit->getPage())
                ->setLimit($pageLimit->getLimit());

            $this->filterService->setPersistentFilter($requestFilter);

            // Must reformat dates *after* persisting otherwise it'll happen again when its reloaded
            if ($requestFilter->getCreatedDateFrom()) {
                $requestFilter->setCreatedDateFrom($this->dateFormatInput($requestFilter->getCreatedDateFrom()));
            }
            if ($requestFilter->getCreatedDateTo()) {
                $requestFilter->setCreatedDateTo($this->dateFormatInput($requestFilter->getCreatedDateTo()));
            }

            /** @var UnimportedListingCollection $listings */
            $listings = $this->listingService->fetchListings($requestFilter);
            $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $listings->getTotal();
            $listings = $this->listingService->alterListingTable($listings, $this->getEvent());

            foreach ($pageLimit->getPageData($listings) as $listing) {
                $data['Records'][] = $listing;
            }
        } catch(NotFound $e) {
            //noop
        }

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function ensureHiddenFilterApplied(array $requestFilter)
    {
        if (!isset($requestFilter['hidden'])) {
            $requestFilter['hidden'] = [false];
        }

        foreach ($requestFilter['hidden'] as $index => $hidden) {
            if ($hidden == 'No') {
                $requestFilter['hidden'][$index] = false;
            }
        }

        return $requestFilter;
    }

    public function hideAction()
    {
        $this->checkAvailability();

        $view = $this->jsonModelFactory->newInstance();

        $listingIds = $this->params()->fromPost('listingIds');
        if (empty($listingIds)){
            $view->setVariable('hidden', false);
            return $view;
        }

        $this->listingService->hideListingsById($listingIds);
        $view->setVariable('hidden', true);
        return $view;
    }

    public function refreshAction()
    {
        $this->checkAvailability();

        $view = $this->jsonModelFactory->newInstance();
        $this->listingService->refresh(
            $this->params()->fromPost('accounts', [])
        );
        return $view;
    }

    public function refreshDetailsAction()
    {
        $this->checkAvailability();

        $accounts = $this->listingService->getRefreshDetails(
            $this->params()->fromPost('accounts', [])
        );

        $hasAmazonAccount = false;
        foreach ($accounts as $index => $account) {
            if (($account['channel'] ?? '') == 'amazon') {
                $hasAmazonAccount = true;
                break;
            }

            // This changes is to be removed after TAC-347 goes live in full.
            if ($account['channel'] == static::CHANNEL_WALMART && !$this->featureFlagService->isActive('Walmart Listings', $this->getOuEntity())) {
                unset($accounts[$index]);
            }
        }

        return $this->jsonModelFactory->newInstance(['hasAmazonAccount' => $hasAmazonAccount, 'accounts' => $accounts]);
    }

    public function createAction()
    {
        $this->checkAvailability();

        $status = new CreationStatus();
        try {
            $this->creationService->createListingFromRawData(
                $status,
                $this->params()->fromPost('accountId', 0),
                $this->params()->fromPost('productId', 0),
                $this->params()->fromPost('listing', [])
            );
        } catch (\Throwable $throwable) {
            if ($throwable instanceof \Exception) {
                $this->logWarningException($throwable, 'Failed to create listing', [], 'ListingCreation');
            } else {
                $this->log($throwable->getMessage(), 'ListingCreation', 'emergency', __NAMESPACE__, $throwable->getTraceAsString());
            }
            $status->error('An unknown error has occurred');
        }
        return $this->jsonModelFactory->newInstance($status->toArray());
    }

    public function importAction()
    {
        $this->checkAvailability();

        $view = $this->jsonModelFactory->newInstance();
        $listingIds = $this->params()->fromPost('listingIds');
        if (empty($listingIds)){
            $view->setVariable('import', false);
            return $view;
        }
        $this->listingService->importListingsById($listingIds);
        $view->setVariable('import', true);
        return $view;
    }

    public function importAllFilteredAction()
    {
        $this->checkAvailability();

        $requestFilter = $this->params()->fromPost('filter', []);
        $requestFilter = $this->ensureHiddenFilterApplied($requestFilter);

        $listingFilter = $this->filterMapper->fromArray($requestFilter);
        $success = $this->listingService->importListingsByFilter($listingFilter);

        return $this->jsonModelFactory->newInstance(['import' => $success]);
    }

    protected function checkAvailability()
    {
        $this->accessUsageExceededService->checkUsage();
        if ($this->listingService->listingImportBlacklisted()) {
            throw new \Exception("Not permitted");
        }
    }

    protected function getOuEntity()
    {
        return $this->organisationUnitService->fetch($this->activeUserContainer->getActiveUserRootOrganisationUnitId());
    }
}
