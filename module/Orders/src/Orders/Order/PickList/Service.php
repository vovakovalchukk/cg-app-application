<?php
namespace Orders\Order\PickList;

use CG\Order\Service\Filter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Product\Service\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Filter as ProductFilter;
use CG\Product\Entity as Product;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Entity as PickListSettings;
use CG\Settings\Picklist\SortValidator;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUserContainer;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    protected $productService;
    protected $pickListSettingsService;
    protected $mapper;
    protected $progressStorage;
    protected $activeUserContainer;

    public function __construct(
        PickListSettingsService $pickListSettingsService,
        ProductService $productService,
        Mapper $mapper,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer
    ) {
        $this->setProductService($productService)
            ->setPickListSettingsService($pickListSettingsService)
            ->setMapper($mapper)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        $pickListSettings = $this->getPickListSettings();

        list($itemsBySku, $itemsByTitle) = $this->aggregateItems($orderCollection, $pickListSettings->getShowSkuless());

        $products = $this->getProductsForSkus(array_keys($itemsBySku));

        $pickListEntries = array_merge(
            $this->getMapper()->fromItemsBySku($itemsBySku, $products, $pickListSettings->getShowPictures()),
            $this->getMapper()->fromItemsByTitle($itemsByTitle)
        );

        $pickListEntries = $this->getMapper()->sortEntries(
            $pickListEntries,
            SortValidator::getSortFieldsNames()[$pickListSettings->getSortField()],
            $pickListSettings->getSortDirection() === SortValidator::SORT_DIRECTION_ASC
        );

        $this->logDebugDump($pickListEntries, 'Pick List', [], 'PICK LIST');
        throw new NotFound();
        return null;
    }


    protected function aggregateItems(OrderCollection $orders, $includeSkuless = false)
    {
        $itemsBySku = [];
        $itemsByTitle = [];

        /** @var Order $order */
        foreach($orders as $order) {
            if($order->getItems()->count() !== 0) {
                /** @var Item $item */
                foreach($order->getItems() as $item) {
                    if($includeSkuless === true && ($item->getItemSku() === null || $item->getItemSku() === '')) {
                        $itemsByTitle[$item->getItemName()][] = $item;
                    } elseif ($item->getItemSku() !== null && $item->getItemSku() !== '') {
                        $itemsBySku[$item->getItemSku()][] = $item;
                    }
                }
            }
        }

        return [$itemsBySku, $itemsByTitle];
    }

    protected function getProductsForSkus(array $skus)
    {
        $filter = new ProductFilter();
        $filter->setSku($skus);

        try {
            return $this->getProductService()->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, __FUNCTION__, ['sku' => $skus]);
        }
    }

    public function checkPickListGenerationProgress($key)
    {
        return (int) $this->getProgressStorage()->getProgress($key);
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
    public function getPickListSettings()
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
}
