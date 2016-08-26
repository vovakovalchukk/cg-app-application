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
        $line2 = $ohOrder->getShippingAddress2ForCourier();
        if ($ohOrder->getShippingAddress3ForCourier()) {
            $line2 .= ', ' . $ohOrder->getShippingAddress3ForCourier();
        }

        return new CAAddress(
            $ohOrder->getShippingAddressCompanyNameForCourier(),
            $firstName,
            $lastName,
            $ohOrder->getShippingAddress1ForCourier(),
            $line2,
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
}
