<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class Order implements FieldsInterface
{
    const FIELDS = [
        'CG Order ID' => 'id',
        'Order ID' => 'externalId',
        'Order Item ID' => 'item.externalId',
        'Sales Channel Name' => 'channel',
        'Account Display Name' => 'accountDisplayName',
        'Purchase Date' => 'purchaseDate',
        'Payment Date' => 'paymentDate',
        'Payment Method' => 'paymentMethod',
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
        'Line VAT' => [
            'field' => 'item.itemTaxPercentage',
            'displayName' => 'Line VAT Percentage'
        ],
        'Line VAT Value' => 'item.lineTaxString',
        'EAN' => 'productDetail.ean',
        'UPC' => 'productDetail.upc',
        'Brand' => 'productDetail.brand',
        'MPN' => 'productDetail.mpn',
        'ASIN' => 'productDetail.asin',
        'ISBN' => 'productDetail.isbn',
        'GTIN' => 'productDetail.gtin',
        'Total Order Discount' => 'totalDiscount',
        'Billing Company Name' => 'calculatedBillingAddressCompanyName',
        'Billing Buyer Name' => 'calculatedBillingAddressFullName',
        'Billing Address Line 1' => 'calculatedBillingAddress1',
        'Billing Address Line 2' => 'calculatedBillingAddress2',
        'Billing Address Line 3' => 'calculatedBillingAddress3',
        'Billing City' => 'calculatedBillingAddressCity',
        'Billing County' => 'calculatedBillingAddressCounty',
        'Billing Country' => 'calculatedBillingAddressCountry',
        'Billing Country Code' => 'calculatedBillingAddressCountryCode',
        'Billing Postcode' => 'calculatedBillingAddressPostcode',
        'Billing Email' => 'calculatedBillingEmailAddress',
        'Billing Telephone' => 'calculatedBillingPhoneNumber',
        'Shipping Company Name' => 'calculatedShippingAddressCompanyName',
        'Shipping Recipient Name' => 'calculatedShippingAddressFullName',
        'Shipping Address Line 1' => 'calculatedShippingAddress1',
        'Shipping Address Line 2' => 'calculatedShippingAddress2',
        'Shipping Address Line 3' => 'calculatedShippingAddress3',
        'Shipping City' => 'calculatedShippingAddressCity',
        'Shipping County' => 'calculatedShippingAddressCounty',
        'Shipping Country' => 'calculatedShippingAddressCountry',
        'Shipping Country Code' => 'calculatedShippingAddressCountryCode',
        'Shipping Postcode' => 'calculatedShippingAddressPostcode',
        'Shipping Email' => 'calculatedShippingEmailAddress',
        'Shipping Telephone' => 'calculatedShippingPhoneNumber',
        'Buyer Message' => 'buyerMessage',
        'Invoice Number' => 'invoiceNumber',
        'VAT Number' => 'vatNumber',
        'Billing Username' => 'externalUsername',
        'Weight' => 'weightString'
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}
