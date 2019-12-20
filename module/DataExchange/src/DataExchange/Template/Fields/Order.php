<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class Order implements FieldsInterface
{
    const FIELDS = [
        'Order ID' => 'externalId',
        'Order Item ID' => 'item.externalId',
        'Sales Channel Name' => 'channel',
        'Purchase Date' => 'purchaseDate',
        'Payment Date' => 'paymentDate',
        'Printed Date' => 'printedDate',
        'Dispatch Date' => 'dispatchDate',
        'Invoice Date' => 'invoiceDate',
        'Status' => 'status',
        'Shipping Price' => 'shippingPrice',
        'Shipping Method' => 'shippingMethod',
        'Currency Code' => 'currencyCode',
        'Item Name' => 'item.itemName',
        'Unit Price' => 'item.individualItemPrice',
        'Quantity' => 'item.itemQuantity',
        'Line Total Price' => 'item.lineTotal',
        'SKU' => 'item.itemSku',
        'Line Discount' => 'item.individualItemDiscountPrice',
        'Line VAT' => 'item.itemTaxPercentage',
        'Total Order Discount' => 'totalDiscount',
        'Billing Company Name' => 'billingAddressCompanyName',
        'Billing Buyer Name' => 'billingAddressFullName',
        'Billing Address Line 1' => 'billingAddress1',
        'Billing Address Line 2' => 'billingAddress2',
        'Billing Address Line 3' => 'billingAddress3',
        'Billing City' => 'billingAddressCity',
        'Billing County' => 'billingAddressCounty',
        'Billing Country' => 'billingAddressCountry',
        'Billing Country Code' => 'billingAddressCountryCode',
        'Billing Postcode' => 'billingAddressPostcode',
        'Billing Email' => 'billingEmailAddress',
        'Billing Telephone' => 'billingPhoneNumber',
        'Shipping Company Name' => 'shippingAddressCompanyName',
        'Shipping Recipient Name' => 'shippingAddressFullName',
        'Shipping Address Line 1' => 'shippingAddress1',
        'Shipping Address Line 2' => 'shippingAddress2',
        'Shipping Address Line 3' => 'shippingAddress3',
        'Shipping City' => 'shippingAddressCity',
        'Shipping County' => 'shippingAddressCounty',
        'Shipping Country' => 'shippingAddressCountry',
        'Shipping Country Code' => 'shippingAddressCountryCode',
        'Shipping Postcode' => 'shippingAddressPostcode',
        'Shipping Email' => 'shippingEmailAddress',
        'Shipping Telephone' => 'shippingPhoneNumber',
        'Buyer Message' => 'buyerMessage',
        'Invoice Number' => 'invoiceNumber',
        'VAT Number' => 'vatNumber',
        'Billing Username' => 'externalUsername',
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}
