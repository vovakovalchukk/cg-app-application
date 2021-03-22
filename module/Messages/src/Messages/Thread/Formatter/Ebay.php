<?php
namespace Messages\Thread\Formatter;

use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Listing\Client\Service as ListingService;
use CG\Listing\Collection as ListingCollection;
use CG\Listing\Entity as Listing;
use CG\Listing\Filter as ListingFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use Messages\Thread\FormatterInterface;
use Products\Module as ProductsModule;
use Zend\View\Helper\Url;

class Ebay implements FormatterInterface
{
    protected const LISTING_ID_PATTERN = '/#(\d{10,})/';
    protected const LISTING_FETCH_LIMIT = 200;

    /** @var ListingService */
    protected $listingService;
    /** @var Url */
    protected $url;

    public function __construct(ListingService $listingService, Url $url)
    {
        $this->listingService = $listingService;
        $this->url = $url;
    }

    public function __invoke(ThreadCollection $threads): array
    {
        $ou = $threads->getFirst()->getOrganisationUnitId();
        $threadIdToExternalListingIdMap = $this->buildThreadIdToExternalListingIdMap($threads);
        $listings = $this->fetchListingsByExternalId(array_unique(array_values($threadIdToExternalListingIdMap)), $ou);

        $overrides = [];
        foreach ($threadIdToExternalListingIdMap as $threadId => $externalListingId) {
            $thread = $threads->getById($threadId);
            /** @var Listing $listing */
            $listing = $listings->getBy('externalId', $externalListingId)->getFirst();
            if (!$listing || !$thread) {
                continue;
            }
            $overrides[$threadId]['detailSubject'] = $this->replaceSubjectListingIdWithProductLink($thread, $listing);
        }

        return $overrides;
    }

    protected function buildThreadIdToExternalListingIdMap(ThreadCollection $threads): array
    {
        $threadIdToListingIdMap = [];
        /** @var Thread $thread */
        foreach ($threads as $thread) {
            if ($externalListingId = $this->getListingExternalIdFromSubject($thread->getSubject())) {
                $threadIdToListingIdMap[$thread->getId()] = $externalListingId;
            }
        }
        return $threadIdToListingIdMap;
    }

    protected function fetchListingsByExternalId(array $externalIds, int $ouId): ListingCollection
    {
        $filter = (new ListingFilter)
            ->setLimit(static::LISTING_FETCH_LIMIT)
            ->setExternalId($externalIds)
            ->setOrganisationUnitId([$ouId]);
        $page = 0;

        $listings = new ListingCollection(Listing::class, __FUNCTION__);
        do {
            $filter->setPage(++$page);
            try {
                $listings->attachAll($this->listingService->fetchCollectionByFilter($filter));
            } catch (NotFound $exception) {
                return $listings;
            }
        } while ($listings->getTotal() > static::LISTING_FETCH_LIMIT * $page);

        return $listings;
    }

    protected function replaceSubjectListingIdWithProductLink(Thread $thread, Listing $listing): string
    {
        $subject = $thread->getSubject();
        $sku = $this->getSkuFromListingExternalId($listing);
        if (!$sku) {
            return $subject;
        }
        $url = ($this->url)(ProductsModule::ROUTE, [], ['query' => ['search' => $sku]]);
        $link = '<a href="' . $url . '">' . $listing->getExternalId() . '</a>';
        return str_replace($listing->getExternalId(), $link, $subject);
    }

    protected function getListingExternalIdFromSubject(string $subject): ?string
    {
        $matches = [];
        if (preg_match(static::LISTING_ID_PATTERN, $subject, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function getSkuFromListingExternalId(Listing $listing): ?string
    {
        $skus = array_filter($listing->getProductSkus());
        if (empty($skus)) {
            return null;
        }
        return array_shift($skus);
    }
}
