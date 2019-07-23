<?php
namespace Settings\ListingTemplate;

use CG\Listing\Template\Collection as ListingTemplateCollection;
use CG\Listing\Template\Entity as ListingTemplate;
use CG\Listing\Template\Filter as ListingTemplateFilter;
use CG\Listing\Template\Service as ListingTemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\TagReplace\Product as ProductTagReplacer;
use CG\User\ActiveUserInterface;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    /** @var ListingTemplateService */
    protected $listingTemplateService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var ProductTagReplacer */
    protected $productTagReplacer;

    public function __construct(
        ListingTemplateService $listingTemplateService,
        ActiveUserInterface $activeUserContainer,
        ProductTagReplacer $productTagReplacer
    ) {
        $this->listingTemplateService = $listingTemplateService;
        $this->activeUserContainer = $activeUserContainer;
        $this->productTagReplacer = $productTagReplacer;
    }

    public function getListingTemplateTags(): array
    {
        $tagOptions = [];
        foreach ($this->productTagReplacer->getAvailableTags() as $name => $value) {
            $tagOptions[] = ['name' => $name, 'value' => $value];
        }
        return $tagOptions;
    }

    public function getUsersTemplates(): ListingTemplateCollection
    {
        try {
            $filter = (new ListingTemplateFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);

            return $this->listingTemplateService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ListingTemplateCollection(ListingTemplate::class, 'empty');
        }
    }
}
