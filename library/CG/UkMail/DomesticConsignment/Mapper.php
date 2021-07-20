<?php
namespace CG\UkMail\DomesticConsignment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Address as CAAddress;
use CG\Locale\CountryNameByAlpha3Code;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Request\Rest\DomesticConsignment as DomesticConsignmentRequest;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Mapper
{
    protected const WEIGHT_UNIT = 'kg';
    protected const DIMENSION_UNIT = 'cm';
    protected const LABEL_FORMAT_PNG6x4 = 'PNG6x4';
    protected const CONTACT_NUMBER_TYPE_PHONE = 'phone';
    protected const CONTACT_NUMBER_TYPE_MOBILE = 'mobile';
    protected const ADDRESS_TYPE_DOORSTEP = 'doorstep';
    protected const ADDRESS_TYPE_RESIDENTIAL = 'residential';
    protected const PRE_DELIVERY_NOTIFICATION_EMAIL = 'email';

    public function createDomesticConsignmentRequest(
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber
    ): DomesticConsignmentRequest {
        $account = $shipment->getAccount();
        $packages = $shipment->getPackages();
        $deliveryAddress = $shipment->getDeliveryAddress();

        return new DomesticConsignmentRequest(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $account->getCredentials()['domesticAccountNumber'],
            $collectionJobNumber,
            $this->getDeliveryDetails($deliveryAddress),
            $shipment->getDeliveryService()->getReference(),
            count($packages),
            $this->getTotalWeight($packages),
            $shipment->getCustomerReference(),
            null,
            $this->getParcels($packages),
            null,
            $this->getRecipient($deliveryAddress),
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

    protected function getDeliveryAddress(CAAddress $address, bool $isRecipientAddress = false): Address
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
            $isRecipientAddress ? static::ADDRESS_TYPE_RESIDENTIAL : static::ADDRESS_TYPE_DOORSTEP
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
        return ceil($totalWeight);
    }

    protected function convertWeight(float $weight): float
    {
        return (new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(static::WEIGHT_UNIT);
    }

    protected function convertDimension(float $dimension): float
    {
        return (new Length($dimension, ProductDetail::UNIT_LENGTH))->toUnit(static::DIMENSION_UNIT);
    }

    /**
     * @param Package[] $packages
     * @return array
     */
    protected function getParcels(array $packages): array
    {
        $parcels = [];
        foreach ($packages as $package) {
            $parcels[] = new Parcel(
                $this->convertDimension($package->getLength()),
                $this->convertDimension($package->getWidth()),
                $this->convertDimension($package->getHeight())
            );
        }

        return $parcels;
    }

    protected function getRecipient(CAAddress $address): Recipient
    {
        return new Recipient(
            $this->getContactName($address),
            $address->getEmailAddress(),
            $address->getPhoneNumber(),
            $this->getDeliveryAddress($address, true),
            static::PRE_DELIVERY_NOTIFICATION_EMAIL
        );
    }
}