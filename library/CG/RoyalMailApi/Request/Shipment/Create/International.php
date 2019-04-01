<?php
namespace CG\RoyalMailApi\Request\Shipment\Create;

use CG\RoyalMailApi\Request\Shipment\Create as CreateRequest;
use CG\RoyalMailApi\Shipment\Package;

class International extends CreateRequest
{
    const COUNTRY_OF_MANUFACTURE = 'GB';

    protected function toArray(): array
    {
        $array = parent::toArray();
        $array['internationalInfo'] = $this->toInternationalArray();
        return $array;
    }

    protected function toInternationalArray(): array
    {
        return [
            'parcels' => $this->toParcelsArray(),
        ];
    }

    protected function toParcelsArray(): array
    {
        $parcels = [];
        /** @var Package $package */
        foreach ($this->shipment->getPackages() as $package) {
            $parcels[] = [
                'weight' => $this->convertWeight($package->getWeight()),
                'contentDetails' => $this->toContentsArray($package),
            ];
        }
    }

    protected function toContentsArray(Package $package): array
    {
        $contentDetails = [];
        foreach ($package->getContents() as $content) {
            $contentDetails[] = [
                'countryOfManufactureCode' => ($content->getOrigin() != 'UK' ? $content->getOrigin() : 'GB'),
                'description' => $content->getDescription(),
                'unitQuantity' => $content->getQuantity(),
                'unitValue' => $content->getUnitValue(),
                'currencyCode' => $content->getUnitCurrency()
            ];
        }
        return $contentDetails;
    }
}