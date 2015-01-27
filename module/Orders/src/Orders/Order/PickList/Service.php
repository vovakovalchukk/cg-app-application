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
        return null;
    }

    protected function filterItems(OrderCollection $orderCollection, $skuless = false)
    {
        $items = new ItemCollection(Item::class, __FUNCTION__, []);
        $skus = [];
        $titles = [];

        /** @var Order $order */
        foreach($orderCollection as $order) {
            if($order->getItems()->count() !== 0) {
                /** @var Item $item */
                foreach($order->getItems() as $item) {
                    if($skuless === true && ($item->getItemSku() === null || $item->getItemSku() === '')) {
                        $items->attach($item);
                        $titles[$item->getItemName()][] = $item->getId();
                    } else {
                        $skus[$item->getItemSku()][] = $item->getId();
                        $items->attach($item);
                    }
                }
            }
        }

        $products = $this->getProductsForSkus(array_keys($skus));
        /** @var Product $product */

        return [$items, $products];
    }

    protected function aggregateItems(array $itemsIdsBySku, array $itemsIdsByTitle, ItemCollection $items, ProductCollection $products)
    {
        $pickListEntries = [];
        foreach($itemsIdsBySku as $sku => $itemsIds) {
            $productCollection = $products->getBy('sku', $sku);
            $productCollection->rewind();
            $matchingProduct = $productCollection->current();
            $matchingItems = $items->getBy('id', $itemsIds);
            /** @var Product $matchingProduct */
            if($matchingProduct === null) {
                $description = $this->getMostDescriptiveItemDetails($matchingItems);
                $title = $description['title'];
                $variation = implode("\n", $description['variationAttributes']);
            } else {
                $title = $matchingProduct->getName();
                $variation = $this->mergeProductVariationAttributes($matchingProduct);
            }

            $pickListEntries[] = [
                'title' => $title,
                'variation' => $variation,
                'quantity' => $this->sumQuantities($matchingItems),
                'sku' => $sku,
                'image' => '' //TODO
            ];
        }
    }

    protected function getMostDescriptiveItemDetails(ItemCollection $matchingItems)
    {
        $matchingItems->rewind();
        $bestTitle= $matchingItems->current()->getItemName();
        $bestVariationAttributes = $matchingItems->current()->getItemVariationAttribute();

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

    protected function sumQuantities(ItemCollection $items)
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getItemQuantity();
        }
        return $sum;
    }

    protected function mergeProductVariationAttributes($matchingProduct)
    {
        return [];
    }

    protected function convertItemsToArray(ItemCollection $items)
    {
        $pickListEntries = [];
        /** @var Item $item */
        foreach($items as $item) {
            $pickListEntries[] = [
                "title" => $item->getItemName(),
                "quantity" => $item->getItemQuantity(),
                "sku" => $item->getItemSku(),
                "variation" => $item->getItemVariationAttribute(),
                "image" => ''
            ];
        }
        return $pickListEntries;
    }

    protected function convertProductsToArray(ProductCollection $products)
    {
        $pickListEntries = [];
        /** @var Product $product */
        foreach($products as $product) {
            $pickListEntries[] = [
                "title" => $product->getName(),
                "quantity" => 0, //TODO
                "sku" => $product->getSku(),
                "variation" => $product->getVariations(), //TODO
                "image" => ''
            ];
        }
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
