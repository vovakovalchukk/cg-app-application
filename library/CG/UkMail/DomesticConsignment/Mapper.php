<?php
namespace CG\UkMail\DomesticConsignment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Request\Rest\DomesticConsignment;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package;
use PhpUnitsOfMeasure\AbstractPhysicalQuantity;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use CG\CourierAdapter\Address as CAAddress;
use CG\Locale\CountryNameByAlpha3Code;

class Mapper
{
    protected const WEIGHT_UNIT = 'kg';
    protected const LABEL_FORMAT_PNG6x4 = 'PNG6x4';
    protected const CONTACT_NUMBER_TYPE_PHONE = 'phone';
    protected const CONTACT_NUMBER_TYPE_MOBILE = 'mobile';
    protected const ADDRESS_TYPE_DOORSTEP = 'doorstep';

    public function createDomesticConsignmentRequest(
        CourierAdapterAccount $account,
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber

    ) {

        $packages = $shipment->getPackages();


        return new DomesticConsignment(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $account->getCredentials()['accountNumber'],
            $collectionJobNumber,
            $this->getDeliveryDetails($shipment->getDeliveryAddress()),
            $shipment->getDeliveryService()->getReference(),
            count($packages),
            $this->getTotalWeight($packages),
            $shipment->getCustomerReference(),
            null,
            $parcels,
            null,
            $recipient,
            false,
            false,
            false,
            null,
            static::LABEL_FORMAT_PNG6x4
        );
    }

    protected function getDeliveryDetails(CAAddress $address): DeliveryInformation
    {
        $deliveryAddresses[] = $this->getDeliveryAddress($address);

        return new DeliveryInformation(
            $this->getContactName($address),
            $address->getPhoneNumber(),
            static::CONTACT_NUMBER_TYPE_MOBILE,
            $address->getEmailAddress(),
            $deliveryAddresses
        );
    }

    protected function getContactName(CAAddress $address): string
    {
        return $address->getFirstName().' '.$address->getLastName();
    }

    protected function getDeliveryAddress(CAAddress $address): Address
    {
        return new Address(
            $address->getCompanyName(),
            $address->getLine1(),
            $address->getLine2(),
            $address->getLine3(),
            $address->determineCityFromAddressLines(),
            $address->determineRegionFromAddressLines(),
            $address->getPostCode(),
            CountryNameByAlpha3Code::getCountryAlpha3CodeFromCountryAlpha2Code($address->getISOAlpha2CountryCode()),
            static::ADDRESS_TYPE_DOORSTEP
        );
    }

    /**
     * Consignment Total weight in whole Kg. Min 1, max 999
     * @param Package[] $packages
     * @return int
     */
    protected function getTotalWeight(array $packages): int
    {
        $totalWeight = 0;

        foreach ($packages as $package) {
            $totalWeight += $this->convertWeight($package->getWeight());
        }
        return celi($totalWeight);
    }

    protected function convertWeight(float $weight): float
    {
        return (new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(static::WEIGHT_UNIT);
    }
}