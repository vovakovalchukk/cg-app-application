<?php
namespace CG\ShipStation\Messages;

use CG\Locale\USAStates;
use CG\Order\Shared\ShippableInterface as Order;
use CG\ShipStation\Messages\Exception\InvalidStateException;

class ShipmentAddress extends Address
{
    /** @var string */
    protected $companyName;
    /** @var bool */
    protected $addressResidentialIndicator;

    public function __construct(
        string $name,
        string $phone,
        string $addressLine1,
        string $cityLocality,
        string $province,
        string $postalCode,
        string $countryCode,
        string $addressLine2 = '',
        string $email = '',
        string $companyName = '',
        string $addressResidentialIndicator = ''
    ) {
        parent::__construct(
            $name,
            $phone,
            $addressLine1,
            $cityLocality,
            $province,
            $postalCode,
            $countryCode,
            $addressLine2,
            $email
        );
        $this->companyName = $companyName;
        $this->addressResidentialIndicator = $addressResidentialIndicator;
    }

    public static function build($decodedJson): ShipmentAddress
    {
        $noEmail = '';
        return new static(
            $decodedJson->name,
            $decodedJson->phone,
            $decodedJson->address_line1,
            $decodedJson->city_locality,
            $decodedJson->state_province,
            $decodedJson->postal_code,
            $decodedJson->country_code,
            $decodedJson->address_line2 ?? '',
            $decodedJson->address_line2 ?? '',
            $noEmail,
            $decodedJson->company_name ?? '',
            $decodedJson->address_residential_indicator ?? ''
        );
    }
    
    public function toArray(): array
    {
        $addressResidentialIndicator = $this->isAddressResidentialIndicator() ? 'yes': 'no';
        if ($this->isAddressResidentialIndicator() === null) {
            $addressResidentialIndicator = 'unknown';
        }

        $array = parent::toArray();
        $array['company_name'] = $this->getCompanyName();
        $array['address_residential_indicator'] = $addressResidentialIndicator;
        return $array;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): ShipmentAddress
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function isAddressResidentialIndicator(): string
    {
        return $this->addressResidentialIndicator;
    }

    /**
     * @return self
     */
    public function setAddressResidentialIndicator(string $addressResidentialIndicator): ShipmentAddress
    {
        $this->addressResidentialIndicator = $addressResidentialIndicator;
        return $this;
    }

    /**
     * @throws InvalidStateException if its a US address but we cant find the state code
     */
    public static function createFromOrder(Order $order): ShipmentAddress
    {
        $addressResidentialIndicatorUnknown = 'unknown';
        return new static(
            $order->getShippingAddressFullNameForCourier(),
            $order->getShippingPhoneNumberForCourier(),
            $order->getShippingAddress1ForCourier(),
            $order->getShippingAddressCityForCourier() ?? '',
            static::getStateProvince($order),
            $order->getShippingAddressPostcodeForCourier(),
            $order->getShippingAddressCountryCodeForCourier(),
            $order->getShippingAddress2ForCourier(),
            $order->getShippingEmailAddressForCourier(),
            $order->getShippingAddressCompanyNameForCourier(),
            $addressResidentialIndicatorUnknown
        );
    }

    protected static function getStateProvince(Order $order): string
    {
        $stateProvince = $order->getShippingAddressCountyForCourier();
        if (!$stateProvince) {
            return '';
        }
        if ($order->getShippingAddressCountryCodeForCourier() != 'US') {
            return $stateProvince;
        }
        // For the US we must use the 2-letter code
        return static::getStateCode($stateProvince);
    }

    protected static function getStateCode(string $state): string
    {
        $states = USAStates::getStates();
        if (strlen($state) == 2 && isset($states[$state])) {
            return $state;
        }
        $state = ucwords(strtolower($state));
        $code = array_search($state, $states);
        if (!$code) {
            throw new InvalidStateException('Could not find 2-letter code for US state ' . $state);
        }
        return $code;
    }
}