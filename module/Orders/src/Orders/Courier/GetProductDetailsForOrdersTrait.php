<?php
namespace Orders\Courier;

use CG\Order\Shared\Collection as OrderCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Filter as ProductDetailFilter;
use CG\Stdlib\Exception\Runtime\NotFound;

trait GetProductDetailsForOrdersTrait
{
    protected function getProductDetailsForOrders(OrderCollection $orders, OrganisationUnit $rootOu)
    {
        $productSkus = [];
        $ouIds = [$rootOu->getId() => true];
        foreach ($orders as $order) {
            $ouIds[$order->getOrganisationUnitId()] = true;
            foreach ($order->getItems() as $item) {
                $productSkus[] = $item->getItemSku();
            }
        }

        $filter = (new ProductDetailFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId(array_keys($ouIds))
            ->setSku($productSkus);
        try {
            return $this->getProductDetailService()->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductDetailCollection(ProductDetail::class, 'empty');
        }
    }

    /**
     * @return \CG\Product\Detail\Service
     */
    abstract protected function getProductDetailService();
}