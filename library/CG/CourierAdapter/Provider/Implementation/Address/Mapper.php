<?php
namespace CG\CourierAdapter\Provider\Implementation\Address;

use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\Address as CAAddress;
use CG\Order\Shared\ShippableInterface as OHOrder;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    const INVALID_ADDRESS_DETAILS_MESSAGE = 'Please fill in your main trading company address details <a href="https://admin.orderhub.io/company" target="_blank">here</a>';

    public function ohOrderToDeliveryAddress(OHOrder $ohOrder)
    {
        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ohOrder->getShippingAddressFullNameForCourier());

        return new CAAddress(
            $ohOrder->getShippingAddressCompanyNameForCourier(),
            $firstName,
            $lastName,
            $ohOrder->getShippingAddress1ForCourier(),
            $ohOrder->getShippingAddress2ForCourier(),
            $ohOrder->getShippingAddress3ForCourier(),
            $ohOrder->getShippingAddressCityForCourier(),
            $ohOrder->getShippingAddressCountyForCourier(),
            $ohOrder->getShippingAddressPostcodeForCourier(),
            $ohOrder->getShippingAddressCountryForCourier(),
            $ohOrder->getShippingAddressCountryCodeForCourier(),
            $ohOrder->getShippingEmailAddressForCourier(),
            $ohOrder->getShippingPhoneNumberForCourier()
        );
    }

    public function organisationUnitToCollectionAddress(OrganisationUnit $ou)
    {
        if ($this->areCriticalAddressLinesEmpty($ou)) {
            throw new InvalidCredentialsException(static::INVALID_ADDRESS_DETAILS_MESSAGE);
        }

        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ou->getAddressFullName());
        $line2 = $ou->getAddress2();
        if ($ou->getAddress3()) {
            $line2 .= ', ' . $ou->getAddress3();
        }

        return new CAAddress(
            $ou->getAddressCompanyName(),
            $firstName,
            $lastName,
            $ou->getAddress1(),
            $line2,
            $ou->getAddressCity(),
            $ou->getAddressCounty(),
            $ou->getAddressPostcode(),
            $ou->getAddressCountry(),
            $ou->getAddressCountryCode(),
            $ou->getEmailAddress(),
            $ou->getPhoneNumber()
        );
    }

    protected function getFirstAndLastNameFromFullName($fullName)
    {
        $nameParts = explode(' ', $fullName);
        $firstName = array_shift($nameParts);
        $lastName = (!empty($nameParts) ? implode(' ', $nameParts) : $firstName);

        return [$firstName, $lastName];
    }

    protected function areCriticalAddressLinesEmpty(OrganisationUnit $ou)
    {
        return empty($ou->getAddressPostcode()) || empty($ou->getAddress1()) || empty($ou->getAddressCompanyName());
    }
}
