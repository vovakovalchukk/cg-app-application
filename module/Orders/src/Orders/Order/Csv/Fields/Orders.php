<?php
namespace Orders\Order\Csv\Fields;

use Orders\Order\Csv\FieldsInterface;

class Orders implements FieldsInterface
{
    protected const FIELDS = [
        'externalId',
        'accountDisplayName',
        'purchaseDate',
        'paymentDate',
        'printedDate',
        'dispatchDate',
        'invoiceDate',
        'channel',
        'status',
        'shippingPrice',
        'shippingMethod',
        'currencyCode',
        'subtotal',
        'tax',
        'totalOrderAndItemsDiscount',
        'total',
        'calculatedBillingAddressCompanyName',
        'calculatedBillingAddressFullName',
        'calculatedBillingAddress1',
        'calculatedBillingAddress2',
        'calculatedBillingAddress3',
        'calculatedBillingAddressCity',
        'calculatedBillingAddressCounty',
        'calculatedBillingAddressCountry',
        'calculatedBillingAddressCountryCode',
        'calculatedBillingAddressPostcode',
        'calculatedBillingEmailAddress',
        'calculatedBillingPhoneNumber',
        'calculatedShippingAddressCompanyName',
        'calculatedShippingAddressFullName',
        'calculatedShippingAddress1',
        'calculatedShippingAddress2',
        'calculatedShippingAddress3',
        'calculatedShippingAddressCity',
        'calculatedShippingAddressCounty',
        'calculatedShippingAddressCountry',
        'calculatedShippingAddressCountryCode',
        'calculatedShippingAddressPostcode',
        'calculatedShippingEmailAddress',
        'calculatedShippingPhoneNumber',
        'buyerMessage',
        'invoiceNumber',
        'vatNumber',
        'externalUsername',
        'shippingTaxString',
        'alert',
        'calculatedFulfilmentAddressCompanyName',
        'calculatedFulfilmentAddressFullName',
        'calculatedFulfilmentAddress1',
        'calculatedFulfilmentAddress2',
        'calculatedFulfilmentAddress3',
        'calculatedFulfilmentAddressCity',
        'calculatedFulfilmentAddressCounty',
        'calculatedFulfilmentAddressCountry',
        'calculatedFulfilmentAddressCountryCode',
        'calculatedFulfilmentAddressPostcode',
        'calculatedFulfilmentEmailAddress',
        'calculatedFulfilmentPhoneNumber',
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}