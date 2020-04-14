<?php
namespace Orders\Order\Csv\Fields;

use Orders\Order\Csv\FieldsInterface;

class OrdersItems implements FieldsInterface
{
    protected const FIELDS = [
        'externalId' => 'Order ID',
        'item.externalId' => 'Order Item ID',
        'accountDisplayName' => 'Sales Channel Name',
        'purchaseDate' => 'Purchase Date',
        'paymentDate' => 'Payment Date',
        'printedDate' => 'Printed Date',
        'dispatchDate' => 'Dispatch Date',
        'invoiceDate' => 'Invoice Date',
        'channel' => 'Channel',
        'status' => 'Status',
        'shippingPrice' => 'Shipping Price',
        'shippingMethod' => 'Shipping Method',
        'currencyCode' => 'Currency Code',
        'itemName' => 'Item Name',
        'individualItemPriceString' => 'Unit Price',
        'itemQuantity' => 'Quantity',
        'itemSku' => 'SKU',
        'item.itemTaxPercentage' => 'VAT %',
        'lineDiscountString' => 'Line Discount',
        'lineTaxString' => 'Line VAT',
        'totalOrderAndItemsDiscountString' => 'Total Order Discount',
        'lineTotalString' => 'Line Total',
        'calculatedBillingAddressCompanyName' => 'Billing Company Name',
        'calculatedBillingAddressFullName' => 'Billing Buyer Name',
        'calculatedBillingAddress1' => 'Billing Address Line 1',
        'calculatedBillingAddress2' => 'Billing Address Line 2',
        'calculatedBillingAddress3' => 'Billing Address Line 3',
        'calculatedBillingAddressCity' => 'Billing City',
        'calculatedBillingAddressCounty' => 'Billing County',
        'calculatedBillingAddressCountry' => 'Billing Country',
        'calculatedBillingAddressCountryCode' => 'Billing Country Code',
        'calculatedBillingAddressPostcode' => 'Billing Postcode',
        'calculatedBillingEmailAddress' => 'Billing Email',
        'calculatedBillingPhoneNumber' => 'Billing Telephone',
        'calculatedShippingAddressCompanyName' => 'Shipping Company Name',
        'calculatedShippingAddressFullName' => 'Shipping Recipient Name',
        'calculatedShippingAddress1' => 'Shipping Address Line 1',
        'calculatedShippingAddress2' => 'Shipping Address Line 2',
        'calculatedShippingAddress3' => 'Shipping Address Line 3',
        'calculatedShippingAddressCity' => 'Shipping City',
        'calculatedShippingAddressCounty' => 'Shipping County',
        'calculatedShippingAddressCountry' => 'Shipping Country',
        'calculatedShippingAddressCountryCode' => 'Shipping Country Code',
        'calculatedShippingAddressPostcode' => 'Shipping Postcode',
        'calculatedShippingEmailAddress' => 'Shipping Email',
        'calculatedShippingPhoneNumber' => 'Shipping Telephone',
        'buyerMessage' => 'Buyer Message',
        'item.giftWrapType' => 'Gift Wrap Type',
        'item.giftWrapMessage' => 'Gift Wrap Message',
        'item.giftWrapPrice' => 'Gift Wrap Price',
        'invoiceNumber' => 'Invoice Number',
        'vatNumber' => 'VAT Number',
        'externalUsername' => 'Billing Username',
        'shippingTax' => 'Shipping VAT',
        'alert' => 'Order Alert',
        'productDetail.cost' => 'Cost Price',
        'calculatedFulfilmentAddressCompanyName' => 'Fulfilment Company Name',
        'calculatedFulfilmentAddressFullName' => 'Fulfilment Recipient Name',
        'calculatedFulfilmentAddress1' => 'Fulfilment Address Line 1',
        'calculatedFulfilmentAddress2' => 'Fulfilment Address Line 2',
        'calculatedFulfilmentAddress3' => 'Fulfilment Address Line 3',
        'calculatedFulfilmentAddressCity' => 'Fulfilment City',
        'calculatedFulfilmentAddressCounty' => 'Fulfilment County',
        'calculatedFulfilmentAddressCountry' => 'Fulfilment Country',
        'calculatedFulfilmentAddressCountryCode' => 'Fulfilment Country Code',
        'calculatedFulfilmentAddressPostcode' => 'Fulfilment Postcode',
        'calculatedFulfilmentEmailAddress' => 'Fulfilment Email',
        'calculatedFulfilmentPhoneNumber' => 'Fulfilment Telephone',
        'weightString' => 'Weight',
    ];

    public static function getFields(): array
    {
        return static::FIELDS;
    }
}