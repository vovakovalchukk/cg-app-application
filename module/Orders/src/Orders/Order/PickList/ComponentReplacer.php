<?php
namespace Orders\Order\PickList;

use CG\FeatureFlags\Feature;
use CG\FeatureFlags\Service as FeatureFlags;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as OrderItems;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\OrganisationUnit\Entity as Ou;
use CG\OrganisationUnit\Service as OuService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as Products;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Product\LinkLeaf\Collection as ProductLinkLeafs;
use CG\Product\LinkLeaf\Entity as ProductLinkLeaf;
use CG\Product\LinkLeaf\Filter as ProductLinkLeafFilter;
use CG\Product\LinkLeaf\Service as ProductLinkLeafService;
use CG\Stdlib\Exception\Runtime\NotFound;

class ComponentReplacer
{
    /** @var OuService $ouService */
    protected $ouService;
    /** @var FeatureFlags $featureFlags */
    protected $featureFlags;
    /** @var ProductLinkLeafService $productLinkLeafService */
    protected $productLinkLeafService;
    /** @var ProductService $productService */
    protected $productService;

    public function __construct(
        OuService $ouService,
        FeatureFlags $featureFlags,
        ProductLinkLeafService $productLinkLeafService,
        ProductService $productService
    ) {
        $this->ouService = $ouService;
        $this->featureFlags = $featureFlags;
        $this->productLinkLeafService = $productLinkLeafService;
        $this->productService = $productService;
    }

    public function __invoke(Orders $orders): Orders
    {
        $replacedOrders = new Orders(Order::class, __FUNCTION__, ['orderIds' => $orders->getIds()]);
        foreach ($this->mapOrdersByRootOu($orders) as $rootOuId => $rootOuOrders) {
            $replacedOrders->attachAll($this->replaceComponentOrderItems($rootOuId, $rootOuOrders));
        }
        return $replacedOrders;
    }

    /**
     * @return Orders[]
     */
    protected function mapOrdersByRootOu(Orders $orders): array
    {
        $map = [];
        foreach ($orders->getArrayOf('rootOrganisationUnitId') as $rootOrganisationUnitId) {
            $map[$rootOrganisationUnitId] = $orders->getBy('rootOrganisationUnitId', $rootOrganisationUnitId);
        }
        return $map;
    }

    protected function replaceComponentOrderItems(int $rootOuId, Orders $orders): Orders
    {
        /** @var Ou $rootOu */
        $rootOu = $this->ouService->fetch($rootOuId);
        if (!$this->featureFlags->isActive(Feature::LINKED_PRODUCTS, $rootOu)) {
            return $orders;
        }

        $productLinkLeafs = $this->getProductLinkLeafs($this->getProductLinkLeafIds($rootOu, $orders));
        $componentProducts = $this->getComponentProducts($rootOu, $this->getComponentSkus($productLinkLeafs));

        $replacedOrders = new Orders(Order::class, __FUNCTION__, ['orderIds' => $orders->getIds()]);
        /** @var Order $order */
        foreach ($orders as $order) {
            $replacedOrdersItems = new OrderItems(OrderItem::class, __FUNCTION__, ['orderId' => [$order->getId()]]);
            /** @var OrderItem $orderItem */
            foreach ($order->getItems() as $orderItem) {
                $productLinkLeaf = $this->getProductLinkLeafForOrderItem($productLinkLeafs, $rootOu, $orderItem);
                if (!$productLinkLeaf) {
                    $replacedOrdersItems->attach($orderItem);
                    continue;
                }

                $componentOrderItems = $this->replaceOrderItemWithComponents(
                    $orderItem,
                    $productLinkLeaf,
                    $componentProducts
                );

                foreach ($componentOrderItems as $componentOrderItem) {
                    $replacedOrdersItems->attach($componentOrderItem);
                }
            }
            $replacedOrders->attach($order->setItems($replacedOrdersItems));
        }
        return $replacedOrders;
    }

    protected function getProductLinkLeafIds(Ou $rootOu, Orders $orders): array
    {
        $productLinkLeafIds = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var OrderItem $orderItem */
            foreach ($order->getItems() as $orderItem) {
                $productLinkLeafIds[ProductLinkLeaf::generateId($rootOu->getId(), $orderItem->getItemSku())] = true;
            }
        }
        return array_keys($productLinkLeafIds);
    }

    protected function getProductLinkLeafs(array $productLinkLeafIds): ProductLinkLeafs
    {
        $filter = (new ProductLinkLeafFilter('all', 1))->setOuIdProductSku($productLinkLeafIds);
        try {
            return $this->productLinkLeafService->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            return new ProductLinkLeafs(ProductLinkLeaf::class, 'fetchCollectionByFilter', $filter->toArray());
        }
    }

    protected function getComponentSkus(ProductLinkLeafs $productLinkLeafs)
    {
        return array_unique(
            array_reduce(
                array_map(
                    function(ProductLinkLeaf $productLinkLeaf) {
                        return array_keys($productLinkLeaf->getStockSkuMap());
                    },
                    iterator_to_array($productLinkLeafs)
                ),
                'array_merge',
                []
            )
        );
    }

    protected function getComponentProducts(Ou $rootOu, array $componentSkus): Products
    {
        $filter = (new ProductFilter('all', 1))->setOrganisationUnitId([$rootOu->getId()])->setSku($componentSkus);
        try {
            return $this->productService->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            return new Products(Product::class, 'fetchCollectionByFilter', $filter->toArray());
        }
    }

    protected function getProductLinkLeafForOrderItem(
        ProductLinkLeafs $productLinkLeafs,
        Ou $rootOu,
        OrderItem $orderItem
    ): ?ProductLinkLeaf {
        return $productLinkLeafs->getById(
            ProductLinkLeaf::generateId(
                $rootOu->getId(),
                $orderItem->getItemSku()
            )
        );
    }

    protected function replaceOrderItemWithComponents(
        OrderItem $orderItem,
        ProductLinkLeaf $productLinkLeaf,
        Products $componentProducts
    ): \Generator {
        $index = 1;
        foreach ($productLinkLeaf->getStockSkuMap() as $sku => $qty) {
            /** @var ?Product $componentProduct */
            $componentProduct = $componentProducts->getBy('sku', $sku)->getFirst();
            yield (clone $orderItem)
                ->setId(sprintf('%s-%d', $orderItem->getId(), $index++))
                ->setItemSku($sku)
                ->setItemName($componentProduct ? $componentProduct->getName() : '')
                ->setItemQuantity($orderItem->getItemQuantity() * $qty)
                ->setImageIds(
                    $componentProduct ? array_column($componentProduct->getImageIds(), 'id', 'order') : []
                );
        }
    }
}