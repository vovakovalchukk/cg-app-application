<?php
namespace CG\CourierAdapter\Provider\Implementation\Address;

use CG\CourierAdapter\Provider\Implementation\Address as CAAddress;
use CG\Order\Shared\Entity as OHOrder;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    public function ohOrderToDeliveryAddress(OHOrder $ohOrder)
    {
        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ohOrder->getShippingAddressFullNameForCourier());

        return new CAAddress(
            $firstName,
            $lastName,
            $ohOrder->getShippingAddress1ForCourier(),
            $ohOrder->getShippingAddress2ForCourier(),
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
        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ou->getAddressFullName());

        return new CAAddress(
            $firstName,
            $lastName,
            $ou->getAddress1(),
            $ou->getAddress2(),
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
}
