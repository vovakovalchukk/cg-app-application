<?php
namespace CG\Intersoft\RoyalMail;

use CG\Channel\Shipping\Provider\Carrier\ShippingService\OptionTypes\PackageType;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\Locale\CountryCode;
use CG\Channel\Shipping\Provider\Carrier\ShippingService\OptionTypes\AddOn;

class DeliveryService implements DeliveryServiceInterface
{
    const SERVICE_FORMATS_DOMESTIC = [
        'F' => 'Large Letter',
        'L' => 'Letter',
        'N' => 'Not Applicable',
        'P' => 'Parcel'
    ];

    const SERVICE_FORMATS_INTERNATIONAL = [
        'E' => 'Parcel',
        'G' => 'Large Letter',
        'N' => 'Not Applicable',
        'P' => 'Letter'
    ];

    const SERVICE_TYPE = [
        '1'	=> 'Royal Mail 24 / 1st Class',
        '2'	=> 'Royal Mail 48 / 2nd Class',
        'D'	=> 'Special Delivery Guaranteed',
        'H'	=> 'HM Forces (BFPO)',
        'I'	=> 'International',
        'R' => 'Tracked Returns',
        'T' => 'Royal Mail Tracked'
    ];

    const SERVICE_TYPE_INTERNATIONAL = 'I';
    const SERVICE_TYPE_HM_FORCES = 'H';

    /** @var string */
    protected $reference;
    /** @var string */
    protected $displayName;
    /** @var string */
    protected $serviceType;
    /** @var string */
    protected $shipmentClass;

    public function __construct(
        string $reference,
        string $displayName,
        string $serviceType,
        string $shipmentClass
    ) {
        $this->reference = $reference;
        $this->displayName = $displayName;
        $this->serviceType = $serviceType;
        $this->shipmentClass = $shipmentClass;
    }

    public static function fromArray(array $array): DeliveryService
    {
        return new static(
            $array['reference'],
            $array['displayName'],
            $array['serviceType'],
            $array['shipmentClass'] ?? Shipment::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getShipmentClass()
    {
        return $this->shipmentClass;
    }

    /**
     * @inheritdoc
     */
    public function createShipment(array $shipmentDetails)
    {
        $shipmentDetails['deliveryService'] = $this;
        return ($this->getShipmentClass())::fromArray($shipmentDetails);
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /** string */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @inheritdoc
     */
    public function isISOAlpha2CountryCodeSupported($isoAlpha2CountryCode)
    {
        $isoAlpha2CountryCode = CountryCode::ensureValidCountryCode($isoAlpha2CountryCode);

        if ($this->hideDomesticService($isoAlpha2CountryCode)) {
            return false;
        }

        if ($this->hideInternationalService($isoAlpha2CountryCode)) {
            return false;
        }

        return true;
    }

    protected function isCountryCodeDomestic(string $countryCode): bool
    {
        return $this->isCountryCodeUkOrGb($countryCode) || $this->isCountryCodeChannelIslands($countryCode);
    }

    protected function isCountryCodeUkOrGb(string $countryCode): bool
    {
        return ($countryCode == 'GB' || $countryCode == 'UK');
    }

    protected function isCountryCodeChannelIslands(string $countryCode): bool
    {
        /* Jersey and Guernsey use domestic shipping methods */
        return ($countryCode == 'JE' || $countryCode == 'GG');
    }

    protected function isInternationalServiceType(string $isoAlpha2CountryCode): bool
    {
        return ($isoAlpha2CountryCode == static::SERVICE_TYPE_INTERNATIONAL);
    }

    protected function isArmedForcesServiceType(string $isoAlpha2CountryCode): bool
    {
        return ($isoAlpha2CountryCode == static::SERVICE_TYPE_HM_FORCES);
    }

    protected function hideInternationalService(string $countryCode): bool
    {
        return ((!$this->isInternationalServiceType($this->getServiceType()) && !$this->isArmedForcesServiceType($this->getServiceType()))
            && !$this->isCountryCodeDomestic($countryCode));
    }

    protected function hideDomesticService(string $isoAlpha2CountryCode): bool
    {
        return (($this->isInternationalServiceType($this->getServiceType()) || $this->isArmedForcesServiceType($this->getServiceType()))
            && $this->isCountryCodeDomestic($isoAlpha2CountryCode));
    }
}