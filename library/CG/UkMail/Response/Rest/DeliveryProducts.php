<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\DeliveryProducts\DeliveryProduct;
use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class DeliveryProducts extends AbstractRestResponse implements ResponseInterface
{
    /** @var DeliveryProduct[] */
    protected $deliveryProducts;

    public function __construct(array $deliveryProducts)
    {
        $this->deliveryProducts = $deliveryProducts;
    }

    public static function createResponse($response): ResponseInterface
    {
        $deliveryProducts = [];
        foreach ($response as $deliveryProduct) {
            $deliveryProducts[] = new DeliveryProduct(
                $deliveryProduct['ProductCode'],
                $deliveryProduct['ProductDescription'],
                $deliveryProduct['TransitTimeDescription'],
                $deliveryProduct['MaxTransitTime'],
                $deliveryProduct['MinTransitTime'],
                $deliveryProduct['Bulky'],
                $deliveryProduct['BusinessUnitCode'],
                $deliveryProduct['ServicePointDelivery'],
                $deliveryProduct['ServicePointTypeList'],
                $deliveryProduct['CustomsDeclaration'],
                $deliveryProduct['SortOrder']
            );
        }

        return new static($deliveryProducts);
    }
}