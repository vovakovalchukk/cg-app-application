<?php
namespace CG\Hermes;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\OperationFailed;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\UserError;
use CG\Hermes\DeliveryService\Option as DeliveryServiceOption;
use CG\Hermes\Shipment;

class DeliveryService implements DeliveryServiceInterface
{
    /** @var string */
    protected $reference;
    /** @var string */
    protected $displayName;
    /** @var bool */
    protected $nextDay;
    /** @var int|null */
    protected $specificDay;
    /** @var array|null */
    protected $countries;
    /** @var array|null */
    protected $options;

    public function __construct(
        string $reference,
        string $displayName,
        bool $nextDay,
        ?int $specificDay = null,
        array $countries = null,
        array $options = null
    ) {
        $this->reference = $reference;
        $this->displayName = $displayName;
        $this->countries = $countries;
        $this->options = $options;
    }

    public static function fromArray(array $array): DeliveryService
    {
        return new static(
            $array['reference'],
            $array['displayName'],
            $array['nextDay'] ?? false,
            $array['specificDay'] ?? null,
            $array['countries'] ?? null,
            $array['options'] ? DeliveryServiceOption::multipleFromArrayOfArrays($array['options']) : null
        );
    }

    public function supportsCountryCode(string $countryCode): bool
    {
        if (!$this->countries) {
            return true;
        }
        return in_array($countryCode, $this->countries);
    }

    public function supportsOption(string $option): bool
    {
        return isset($this->options[$option]);
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
        if (!$this->countries) {
            return true;
        }
        return in_array($isoAlpha2CountryCode, $this->countries);
    }
}