<?php
namespace Messages\Thread\Formatter;

use CG\Communication\Thread\Entity as Thread;
use CG\Listing\Client\Service as ListingService;
use CG\Listing\Entity as Listing;
use CG\Listing\Filter as ListingFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use Messages\Thread\FormatterInterface;
use Products\Module as ProductsModule;
use Zend\View\Helper\Url;

class Ebay implements FormatterInterface
{
    protected const LISTING_ID_PATTERN = '/#(\d{10,})/';

    /** @var ListingService */
    protected $listingService;
    /** @var Url */
    protected $url;

    public function __construct(ListingService $listingService, Url $url)
    {
        $this->listingService = $listingService;
        $this->url = $url;
    }

    public function __invoke(array $threadData, Thread $thread): array
    {
        $threadData['detailSubject'] = $this->replaceSubjectListingIdWithProductLink($thread);
        return $threadData;
    }

    protected function replaceSubjectListingIdWithProductLink(Thread $thread): string
    {
        $subject = $thread->getSubject();
        $listingExternalId = $this->getListingExternalIdFromSubject($subject);
        if (!$listingExternalId) {
            return $subject;
        }
        $sku = $this->getSkuFromListingExternalId($listingExternalId);
        if (!$sku) {
            return $subject;
        }
        $url = ($this->url)(ProductsModule::ROUTE, [], ['query' => ['search' => $sku]]);
        $link = '<a href="' . $url . '">' . $listingExternalId . '</a>';
        return str_replace($listingExternalId, $link, $subject);
    }

    protected function getListingExternalIdFromSubject(string $subject): ?string
    {
        $matches = [];
        if (preg_match(static::LISTING_ID_PATTERN, $subject, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function getSkuFromListingExternalId(string $listingExternalId): ?string
    {
        $listing = $this->fetchListingByExternalId($listingExternalId);
        if (!$listing) {
            return null;
        }
        $skus = array_filter($listing->getProductSkus());
        if (empty($skus)) {
            return null;
        }
        return array_shift($skus);
    }

    protected function fetchListingByExternalId(string $externalId): ?Listing
    {
        try {
            $filter = (new ListingFilter(1, 1))->setExternalId([$externalId]);
            return $this->listingService->fetchCollectionByFilter($filter)->getFirst();
        } catch (NotFound $e) {
            return null;
        }
    }
}