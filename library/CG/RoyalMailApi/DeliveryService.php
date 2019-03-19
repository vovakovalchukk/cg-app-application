<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\RoyalMail\DeliveryService\Option as DeliveryServiceOption;
use CG\Locale\CountryCode;

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

    /** @var string */
    protected $reference;
    /** @var string */
    protected $displayName;
    /** @var PackageTypesInterface[] */
    protected $format;
    protected $occurrence;
    protected $offering;
    protected $signature;
    /** @var AddOnsTrait[] */
    protected $enhancements;
    protected $additionalProperties;

    public function __construct(
        string $reference,
        string $displayName
    ) {
        $this->reference = $reference;
        $this->displayName = $displayName;
    }

    public static function fromArray(array $array): DeliveryService
    {
        return new static(
            $array['reference'],
            $array['displayName']
        );
    }

    /**
     * @inheritdoc
     */
    public function getShipmentClass()
    {
        return Shipment::class;
    }

    /**
     * @inheritdoc
     */
    public function createShipment(array $shipmentDetails)
    {
        $shipmentDetails['deliveryService'] = $this;
        return Shipment::fromArray($shipmentDetails);
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

    /**
     * @inheritdoc
     */
    public function isISOAlpha2CountryCodeSupported($isoAlpha2CountryCode)
    {
        // For now we're only supporting EU countries.
        // If we decide to support other countries we'll have to do work to get the required HS Codes from the user.
        // See comments on TAC-172.
        if (!CountryCode::isEUCountryCode($isoAlpha2CountryCode)) {
            return false;
        }
        if (!$this->countries) {
            return true;
        }
        return in_array($isoAlpha2CountryCode, $this->countries);
    }
}