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
use CG\Image\Entity as Image;
use CG\Template\Element\Image as ImageElement;
use CG\Settings\PickList\Service as PickListSettingsService;
use CG\Settings\PickList\Entity as PickListSettings;
use CG\Settings\Picklist\SortValidator;
use CG\PickList\Entity as PickList;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use Orders\Order\Service as OrderService;

class Service implements LoggerAwareInterface, StatsAwareInterface
{
    use LogTrait;
    use StatsTrait;

    protected $orderService;
    protected $productService;
    protected $pickListSettingsService;
    protected $progressStorage;
    protected $activeUserContainer;

    public function __construct(
        OrderService $orderService,
        PickListSettingsService $pickListSettingsService,
        ProductService $productService,
        ProgressStorage $progressStorage,
        ActiveUserContainer $activeUserContainer
    ) {
        $this->setOrderService($orderService)
            ->setProductService($productService)
            ->setPickListSettingsService($pickListSettingsService)
            ->setProgressStorage($progressStorage)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function getResponseFromOrderCollection(OrderCollection $orderCollection, $progressKey = null)
    {
        $pickListSettings = $this->getPickListSettings();

        list($itemsBySku, $itemsByTitle) = $this->aggregateItems($orderCollection, $pickListSettings->getShowSkuless());

        $products = $this->getProductsForSkus(array_keys($itemsBySku));

        $pickListEntries = $this->convertToPickList($itemsBySku, $itemsByTitle, $products, $pickListSettings->getShowPictures());

        $pickListEntries = $this->sortEntries(
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

    protected function convertToPickList(array $itemsBySku, array $itemsByTitle, ProductCollection $products, $includeImages = true)
    {
        $pickListEntries = [];

        foreach($itemsBySku as $sku => $matchingItems) {
            $productCollection = $products->getBy('sku', $sku);
            $productCollection->rewind();
            $matchingProduct = $productCollection->current();

            /** @var Product $matchingProduct */
            if($matchingProduct === null) {
                $description = $this->getMostDescriptiveItemDetails($matchingItems);
                $title = $description['title'];
                $variation = $this->formatAttributes($description['variationAttributes']);
                $image = null;
            } else {
                if(($matchingProduct->getName() === '' || $matchingProduct->getName() === null)
                    && $matchingProduct->getParentProductId() !== 0
                ) {
                    $parentProduct = $this->getProductService()->fetch($matchingProduct->getParentProductId());
                    $title = $parentProduct->getName();
                } else {
                    $title = $matchingProduct->getName();
                }
                $variation = $this->formatAttributes($matchingProduct->getAttributeValues());
                $image = null;
                if($includeImages === true && $matchingProduct->getImages() !== null && $matchingProduct->getImages()->count() !== 0) {
                    $matchingProduct->getImages()->rewind();
                    $image = $this->convertImageToTemplateElement($matchingProduct->getImages()->current());
                }
            }

            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                $sku,
                $variation,
                $image
            );
        }

        foreach($itemsByTitle as $title => $matchingItems) {
            $pickListEntries[] = new PickList(
                $title,
                $this->sumQuantities($matchingItems),
                '',
                $this->formatAttributes($this->getMostDescriptiveItemDetails($matchingItems)['variationAttributes']),
                null
            );
        }

        return $pickListEntries;
    }

    protected function sortEntries(array $pickListEntries, $field, $ascending = true)
    {
        uasort($pickListEntries, function($a, $b) use ($field, $ascending) {
            $getter = 'get' . ucfirst(strtolower($field));
            $directionChanger = ($ascending === false) ? -1 : 1;

            if(is_string($a->$getter())) {
                return $directionChanger * strcasecmp($a->$getter(), $b->$getter());
            }

            return $directionChanger * ($a->$getter() - $b->$getter());
        });

        return $pickListEntries;
    }

    protected function formatAttributes(array $attributes)
    {
        $mergedKeyVals = [];
        foreach($attributes as $attribute => $value) {
            $mergedKeyVals[] = $attribute . ': ' . $value;
        }
        return implode("\n", $mergedKeyVals);
    }

    protected function getMostDescriptiveItemDetails(array $matchingItems)
    {
        $bestTitle= $matchingItems[0]->getItemName();
        $bestVariationAttributes = $matchingItems[0]->getItemVariationAttribute();

        foreach($matchingItems as $item) {
            /** @var Item $item */

            $bestTitle = (strlen($item->getItemName()) > strlen($bestTitle)) ? $item->getItemName() : $bestTitle;

            $bestVariationAttributes =
                (count($item->getItemVariationAttribute()) > count($bestVariationAttributes)) ?
                    $item->getItemVariationAttribute() : $bestVariationAttributes;
        }

        return [
            'title' => $bestTitle,
            'variationAttributes' => $bestVariationAttributes
        ];
    }

    protected function sumQuantities(array $items)
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getItemQuantity();
        }
        return $sum;
    }

    protected function convertImageToTemplateElement(Image $image)
    {
        return new ImageElement(
            base64_encode(file_get_contents($image->getUrl())),
            strtolower(array_pop(explode('.', $image->getUrl())))
        );
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

    protected function getParentProduct(Product $product)
    {

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
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @param OrderService $orderService
     * @return $this
     */
    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
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
