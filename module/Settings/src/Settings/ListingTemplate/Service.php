<?php
namespace Settings\ListingTemplate;

use CG\Listing\Template\Collection as ListingTemplateCollection;
use CG\Listing\Template\Entity as ListingTemplate;
use CG\Listing\Template\Filter as ListingTemplateFilter;
use CG\Listing\Template\Mapper as ListingTemplateMapper;
use CG\Listing\Template\Service as ListingTemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\TagReplace\Product as ProductTagReplacer;
use CG\User\ActiveUserInterface;

class Service
{
    const FEATURE_FLAG = 'Ebay Listing Templates';

    /** @var ListingTemplateService */
    protected $listingTemplateService;
    /** @var ListingTemplateMapper */
    protected $listingTemplateMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var ProductTagReplacer */
    protected $productTagReplacer;

    public function __construct(
        ListingTemplateService $listingTemplateService,
        ListingTemplateMapper $listingTemplateMapper,
        ActiveUserInterface $activeUserContainer,
        ProductTagReplacer $productTagReplacer
    ) {
        $this->listingTemplateService = $listingTemplateService;
        $this->listingTemplateMapper = $listingTemplateMapper;
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

    public function saveFromPostData(array $data): ListingTemplate
    {
        if (isset($data['id']) && (int)$data['id'] > 0) {
            $template = $this->listingTemplateService->fetch($data['id']);
            return $this->updateFromPostData($template, $data);
        }
        return $this->createFromPostData($data);
    }

    protected function createFromPostData(array $data): ListingTemplate
    {
        $data['organisationUnitId'] = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $template = $this->listingTemplateMapper->fromArray($data);
        return $this->save($template);
    }

    protected function updateFromPostData(ListingTemplate $template, array $data): ListingTemplate
    {
        $template->setStoredETag($data['etag']);
        unset($data['id'], $data['etag']);
        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);
            $template->{$setter}($value);
        }
        return $this->save($template);
    }

    protected function save(ListingTemplate $template): ListingTemplate
    {
        $savedTemplateHal = $this->listingTemplateService->save($template);
        $savedTemplate = $this->listingTemplateMapper->fromHal($savedTemplateHal);
        // Annoyingly save doesn't return an etag, have to fetch
        return $this->listingTemplateService->fetch($savedTemplate->getId());
    }
}
