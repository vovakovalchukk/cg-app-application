<?php
namespace Orders\Order\PickList;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Filter as ProductFilter;
use CG\Product\Entity as Product;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Entity as PickListSettings;
use CG\Settings\Picklist\SortValidator;
use CG\PickList\Service as PickListService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use Orders\Order\PickList\Image\Client as ImageClient;
use Orders\Order\PickList\Image\Map as ImageMap;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected $productService;
    protected $pickListService;
    protected $pickListSettingsService;
    protected $imageClient;
    protected $mapper;
    protected $progressStorage;
    protected $activeUserContainer;

    public function __construct(
        ProductService $productService,
        PickListService $pickListService,
        PickListSettingsService $pickListSettingsService,
        ImageClient $imageClient,
        Mapper $mapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer
    ) {
        $this->setProductService($productService)
            ->setPickListService($pickListService)
            ->setPickListSettingsService($pickListSettingsService)
            ->setImageClient($imageClient)
            ->setMapper($mapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        $pickListSettings = $this->getPickListSettings();
        $pickListEntries = $this->getPickListEntries($orderCollection, $pickListSettings);

        if($pickListSettings->getShowPictures()) {
            $content = $this->getPickListService()->renderTemplate($pickListEntries, $this->getActiveUserContainer()->getActiveUser());
        } else {
            $content = $this->getPickListService()->renderTemplateWithoutImages($pickListEntries, $this->getActiveUserContainer()->getActiveUser());
        }
        return new Response(PickListService::MIME_TYPE, PickListService::FILENAME, $content);
    }

    public function checkPickListGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
    }

    protected function getPickListEntries(OrderCollection $orderCollection, PickListSettings $pickListSettings)
    {
        $aggregator = new ItemAggregator($orderCollection, $pickListSettings->getShowSkuless());
        $aggregator();

        $products = $this->fetchProductsForSkus($aggregator->getSkus());
        $parentProducts = $this->fetchParentProductsForProducts($products);

        $pickListEntries = array_merge(
            $this->getMapper()->fromItemsAndProductsBySku(
                $aggregator->getItemsIndexedBySku(),
                $products,
                $parentProducts,
                ($pickListSettings->getShowPictures()) ? $this->fetchImagesForProducts($products) : null
            ),
            $this->getMapper()->fromItemsByTitle(
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

            if(is_string($a->$getter())) {
                return $directionChanger * strcasecmp($a->$getter(), $b->$getter());
            }

            if($a->$getter() === $b->$getter()) {
                return 0;
            }
            $compareValue = ($a->$getter() > $b->$getter()) ? 1 : -1;
            return $directionChanger * $compareValue;
        });

        return $pickListEntries;
    }

    protected function fetchProductsForSkus(array $skus)
    {
        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        $filter = new ProductFilter();
        $filter->setLimit('all');
        $filter->setPage(1);
        $filter->setSku($skus);
        $filter->setOrganisationUnitId([$organisationUnitId]);

        try {
            return $this->getProductService()->fetchCollectionByFilter($filter);
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

        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        $filter = new ProductFilter();
        $filter->setLimit('all');
        $filter->setPage(1);
        $filter->setId($parentIds);
        $filter->setOrganisationUnitId([$organisationUnitId]);

        try {
            return $this->getProductService()->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__, ['id' => $parentIds]);
        }
    }

    protected function fetchImagesForProducts(ProductCollection $products)
    {
        $imageMap = new ImageMap();
        foreach($products as $product) {
            if($product->getImages() === null || $product->getImages()->count() === 0) {
                continue;
            }

            $product->getImages()->rewind();
            $image = $product->getImages()->current();
            $imageMap->setUrlForSku($product->getSku(), $image->getUrl());
        }

        return $this->getImageClient()->fetchImages($imageMap);
    }

    protected function updatePickListGenerationProgress($key, $count)
    {
        if (!$key) {
            return;
        }
        $this->getProgressStorage()->setProgress($key, $count);
    }

    /**
     * @return PickListSettings
     */
    protected function getPickListSettings()
    {
        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        return $this->getPickListSettingsService()->fetch($organisationUnitId);
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    /**
     * @param ProductService $productService
     * @return $this
     */
    public function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return PickListSettingsService
     */
    protected function getPickListSettingsService()
    {
        return $this->pickListSettingsService;
    }

    /**
     * @param PickListSettingsService $pickListSettingsService
     * @return $this
     */
    public function setPickListSettingsService(PickListSettingsService $pickListSettingsService)
    {
        $this->pickListSettingsService = $pickListSettingsService;
        return $this;
    }

    /**
     * @return PickListService
     */
    protected function getPickListService()
    {
        return $this->pickListService;
    }

    /**
     * @param PickListService $pickListService
     * @return $this
     */
    public function setPickListService(PickListService $pickListService)
    {
        $this->pickListService = $pickListService;
        return $this;
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param Mapper $mapper
     * @return $this
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getProgressStorage()
    {
        return $this->progressStorage;
    }

    /**
     * @param ProgressStorage $progressStorage
     * @return $this
     */
    public function setProgressStorage(ProgressStorage $progressStorage)
    {
        $this->progressStorage = $progressStorage;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    public function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ImageClient
     */
    protected function getImageClient()
    {
        return $this->imageClient;
    }

    /**
     * @param ImageClient $imageClient
     * @return $this
     */
    public function setImageClient(ImageClient $imageClient)
    {
        $this->imageClient = $imageClient;
        return $this;
    }
}