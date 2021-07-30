<?php
namespace CG\UkMail\DeliveryProducts;

use CG\Locale\CountryNameByAlpha3Code;
use CG\Product\Detail\Entity as ProductDetail;
use CG\UkMail\Consignment\Domestic\Mapper as DomesticConsignmentMapper;
use CG\UkMail\Request\Rest\DeliveryProducts as DeliveryProductsRequest;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package as UkMailPackage;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Mapper
{
    protected const WEIGHT_UNIT = 'kg';
    protected const DIMENSION_UNIT = 'cm';
    protected const DOORSTEP_ONLY = true;

    public function createDeliveryProductsRequest(Shipment $shipment):DeliveryProductsRequest {
        $account = $shipment->getAccount();
        $packages = $shipment->getPackages();
        $deliveryAddress = $shipment->getDeliveryAddress();

        /** @var UkMailPackage $package */
        $package = current($packages);

        return new DeliveryProductsRequest(
            $account->getCredentials()['apiKey'],
            CountryNameByAlpha3Code::getCountryAlpha3CodeFromCountryAlpha2Code($deliveryAddress->getISOAlpha2CountryCode()),
            $this->convertWeight($package->getWeight()),
            $this->convertDimension($package->getLength()),
            $this->convertDimension($package->getWidth()),
            $this->convertDimension($package->getHeight()),
            DomesticConsignmentMapper::ADDRESS_TYPE_RESIDENTIAL,
            $deliveryAddress->getPostCode(),
            static::DOORSTEP_ONLY
        );
    }

    protected function convertWeight(float $weight): float
    {
        return number_format((new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(static::WEIGHT_UNIT), 2);
    }

    protected function convertDimension(float $dimension): int
    {
        return ceil((new Length($dimension, ProductDetail::UNIT_LENGTH))->toUnit(static::DIMENSION_UNIT));
    }

    public function getDeliveryProductsService204(): DeliveryProduct
    {
        return new DeliveryProduct(
            204,
            'International Road Economy',
            '2-4 days',
            4,
            2,
            '',
            6,
            '',
            [],
            'full',
            1
        );
    }
}