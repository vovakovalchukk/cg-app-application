<?php
namespace Settings\ListingTemplate;

use CG\Listing\Template\Collection as ListingTemplateCollection;
use CG\Listing\Template\Entity as ListingTemplate;
use CG\Listing\Template\Filter as ListingTemplateFilter;
use CG\Listing\Template\Service as ListingTemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    /** @var ListingTemplateService */
    protected $listingTemplateService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(ListingTemplateService $listingTemplateService, ActiveUserInterface $activeUserContainer)
    {
        $this->listingTemplateService = $listingTemplateService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function getListingTemplateTags(): array
    {
        return [
            ['id'=> 1, 'tag' => 'title'],
            ['id'=> 2, 'tag' => 'description'],
            ['id'=> 2, 'tag' => 'bin'],
            ['id'=> 2, 'tag' => 'brand'],
            ['id'=> 2, 'tag' => 'manufacturer'],
            ['id'=> 2, 'tag' => 'barcode'],
            ['id'=> 2, 'tag' => 'weight'],
            ['id'=> 2, 'tag' => 'tax'],
            ['id'=> 2, 'tag' => 'condition'],
            ['id'=> 2, 'tag' => 'image1'],
            ['id'=> 2, 'tag' => 'image2'],
            ['id'=> 2, 'tag' => 'image3'],
            ['id'=> 2, 'tag' => 'image4'],
            ['id'=> 2, 'tag' => 'image5'],
            ['id'=> 2, 'tag' => 'image6'],
            ['id'=> 2, 'tag' => 'image7'],
            ['id'=> 2, 'tag' => 'image8'],
            ['id'=> 2, 'tag' => 'image9'],
            ['id'=> 2, 'tag' => 'image10']
        ];
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
