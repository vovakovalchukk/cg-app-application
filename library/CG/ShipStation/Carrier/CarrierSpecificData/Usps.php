<?php
namespace CG\ShipStation\Carrier\CarrierSpecificData;

use CG\Account\Shared\Entity as AccountEntity;
use CG\ShipStation\Carrier\CarrierSpecificDataInterface;
use CG\ShipStation\Carrier\BookingOption\Usps as BookingOptionProvider;

class Usps implements CarrierSpecificDataInterface
{
    /** @var BookingOptionProvider */
    protected $bookingOptionProvider;

    public function __construct(BookingOptionProvider $bookingOptionProvider)
    {
        $this->bookingOptionProvider = $bookingOptionProvider;
    }

    public function getCarrierSpecificData(array $data, AccountEntity $account): ?array
    {
        $service = $data[0]['service'];
        foreach ($data as &$row) {
            $servicePackageTypes = $this->bookingOptionProvider->getPossiblePackageTypesForService($service);
            $packageTypes = $this->bookingOptionProvider->preparePackageTypesForView($servicePackageTypes, $servicePackageTypes->getFirst());
            $row['packageTypes'] = $packageTypes;
        }
        return $data;
    }
}