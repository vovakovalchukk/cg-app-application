<?php
namespace Orders\Order\PickList;

use CG\Image\Service as ImageService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\PickList\Service as PickListService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Settings\PickList\Entity as PickListSettings;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\Picklist\SortValidator;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\Image\ClientInterface as ImageClient;
use CG\Template\Image\Map as ImageMap;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\Zend\Stdlib\Http\FileResponse as Response;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const EVENT_PICKING_LIST_PRINTED = 'Picking List Printed';

    /** @var ProductService $productService */
    protected $productService;
    /** @var PickListService $pickListService */
    protected $pickListService;
    /** @var PickListSettingsService $pickListSettingsService */
    protected $pickListSettingsService;
    /** @var ImageClient $imageClient */
    protected $imageClient;
    /** @var Mapper $mapper */
    protected $mapper;
    /** @var ProgressStorage $progressStorage */
    protected $progressStorage;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var IntercomEventService $intercomEventService */
    protected $intercomEventService;
    /** @var ImageService $imageService */
    protected $imageService;
    /** @var ComponentReplacer $componentReplacer */
    protected $componentReplacer;

    public function __construct(
        ProductService $productService,
        PickListService $pickListService,
        PickListSettingsService $pickListSettingsService,
        ImageClient $imageClient,
        Mapper $mapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer,
        IntercomEventService $intercomEventService,
        ImageService $imageService,
        ComponentReplacer $componentReplacer
    ) {
        $this->productService = $productService;
        $this->pickListService = $pickListService;
        $this->pickListSettingsService = $pickListSettingsService;
        $this->imageClient = $imageClient;
        $this->mapper = $mapper;
        $this->progressStorage = $progressStorage;
        $this->activeUserContainer = $activeUserContainer;
        $this->intercomEventService = $intercomEventService;
        $this->imageService = $imageService;
        $this->componentReplacer = $componentReplacer;
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        $pickListSettings = $this->getPickListSettings();
        $pickListEntries = $this->getPickListEntries($orderCollection, $pickListSettings);

        $render = 'renderTemplateWithoutImages';
        if ($pickListSettings->getShowPictures()) {
            $render = 'renderTemplate';
        }

        $content = $this->pickListService->{$render}($pickListEntries, $this->activeUserContainer->getActiveUser());
        $response = new Response(PickListService::MIME_TYPE, PickListService::FILENAME, $content);
        $this->notifyOfGeneration();

        return $response;
    }

    public function checkPickListGenerationProgress($key)
    {
        return (int) $this->progressStorage->getProgress($key);
    }

    protected function getPickListEntries(OrderCollection $orderCollection, PickListSettings $pickListSettings)
    {
        $aggregator = new ItemAggregator(($this->componentReplacer)($orderCollection), $pickListSettings->getShowSkuless());
        $aggregator();

        $products = $this->fetchProductsForSkus($aggregator->getSkus());
        $parentProducts = $this->fetchParentProductsForProducts($products);
        $itemsBySku = $aggregator->getItemsIndexedBySku();

        $pickListEntries = array_merge(
            $this->mapper->fromItemsAndProductsBySku(
                $itemsBySku,
                $products,
                $parentProducts,
                ($pickListSettings->getShowPictures()) ? $this->fetchImagesForItems($itemsBySku) : null
            ),
            $this->mapper->fromItemsByTitle(
                $aggregator->getItemsIndexedByTitle()
            )
        );

        $pickListEntries = $this->sortEntries(
            $pickListEntries,
            SortValidator::getSortFieldsNames()[$pickListSettings->getSortField()],
            $pickListSettings->getSortDirection() === SortValidator::SORT_DIRECTION_ASC
        );

        return $pickListEntries;
    }

    protected function sortEntries(array $pickListEntries, $field, $ascending = true)
    {
        usort($pickListEntries, function($a, $b) use ($field, $ascending) {
            $getter = 'get' . ucfirst(strtolower($field));
            $directionChanger = ($ascending === false) ? -1 : 1;

            if (is_string($a->$getter())) {
                return $directionChanger * strcasecmp($a->$getter(), $b->$getter());
            }

            if ($a->$getter() === $b->$getter()) {
                return 0;
            }
            $compareValue = ($a->$getter() > $b->$getter()) ? 1 : -1;
            return $directionChanger * $compareValue;
        });

        return $pickListEntries;
    }

    protected function fetchProductsForSkus(array $skus)
    {
        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = new ProductFilter();
        $filter->setLimit('all');
        $filter->setPage(1);
        $filter->setSku($skus);
        $filter->setOrganisationUnitId([$organisationUnitId]);

        try {
            return $this->productService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__, ['sku' => $skus]);
        }
    }

    protected function fetchParentProductsForProducts(ProductCollection $products)
    {
        $parentIds = [];
        foreach($products as $product) {
            if ($product->getParentProductId() !== 0) {
                $parentIds[] = $product->getParentProductId();
            }
        }
        if (empty($parentIds)) {
            return new ProductCollection(Product::class, __FUNCTION__, ['id' => $parentIds]);
        }

        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = new ProductFilter();
        $filter->setLimit('all');
        $filter->setPage(1);
        $filter->setId($parentIds);
        $filter->setOrganisationUnitId([$organisationUnitId]);

        try {
            return $this->productService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__, ['id' => $parentIds]);
        }
    }

    protected function fetchImagesForItems(array $itemsBySku)
    {
        $map = [];
        foreach($itemsBySku as $sku => $items) {
            /** @var Item $item */
            foreach($items as $item) {
                foreach ($item->getImageIds() as $imageId) {
                    $map[$sku] = $imageId;
                    continue 2;
                }
            }
        }

        $imageMap = new ImageMap();
        $this->imageService->populateImageMapBySku($imageMap, $map);
        $this->imageClient->fetchImages($imageMap);

        return $imageMap;
    }

    protected function updatePickListGenerationProgress($key, $count)
    {
        if (!$key) {
            return;
        }
        $this->progressStorage->setProgress($key, $count);
    }

    /**
     * @return PickListSettings
     */
    protected function getPickListSettings()
    {
        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        return $this->pickListSettingsService->fetch($organisationUnitId);
    }

    protected function notifyOfGeneration()
    {
        $event = new IntercomEvent(static::EVENT_PICKING_LIST_PRINTED, $this->activeUserContainer->getActiveUser()->getId());
        $this->intercomEventService->save($event);
    }
}
