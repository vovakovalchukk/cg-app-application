<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Entity as Order;

class Mapper
{
    protected static $commonHeaders = [
        'Order ID' => 'externalId',
        'Sales Channel Name' => 'accountId',
        'Purchase Date' => 'purchaseDate',
        'Payment Date' => 'paymentDate',
        'Printed Date' => 'printedDate',
        'Dispatch Date' => 'dispatchDate',
        'Channel' => 'channel',
        'Status' => 'status',
        'Shipping Price' => 'shippingPrice',
        'Shipping Method' => 'shippingMethod',
        'Currency Code' => 'currencyCode',
        '_specific_headers' => null,
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
        'Buyer Message' => 'buyerMessage'
    ];

    protected static $headers = [
        'Order ID',
        'Sales Channel Name',
        'Purchase Date',
        'Payment Date',
        'Printed Date',
        'Dispatch Date',
        'Channel',
        'Status',
        'Shipping Price',
        'Shipping Method',
        'Currency Code',
        'Item Name',
        'Unit Price',
        'Quantity',
        'SKU',
        'VAT %',
        'Line Discount',
        'Line Vat',
        'Total Order Discount',
        'Line Total',
        'Billing Company Name',
        'Billing Buyer Name',
        'Billing Address Line 1',
        'Billing Address Line 2',
        'Billing Address Line 3',
        'Billing City',
        'Billing County',
        'Billing Country',
        'Billing Country Code',
        'Billing Postcode',
        'Billing Email',
        'Billing Telephone',
        'Shipping Company Name',
        'Shipping Recipient Name',
        'Shipping Address Line 1',
        'Shipping Address Line 2',
        'Shipping Address Line 3',
        'Shipping City',
        'Shipping County',
        'Shipping Country',
        'Shipping Country Code',
        'Shipping Postcode',
        'Shipping Email',
        'Shipping Telephone',
        'Buyer Message'
    ];

    public function getOrderSpecificHeaders()
    {
        return [
            'Subtotal' => function(Order $order) {
                return $order->getSubtotal();
            },
            'Total VAT' => '',
            'Total Discount' => 'totalDiscount',
            'Total' => 'total'
        ];
    }

    public function getItemSpecificHeaders()
    {
        return [
            'Item Name' => function(Order $order) {
                //itemName
            },
            'Unit Price' => function(Order $order){
                //'individualItemPrice'
            } ,
            'Quantity' => function(Order $order) {
                //'itemQuantity',
            },
            'SKU' => function(Order $order) {
                //'itemSku'
            },
            'VAT %' => function(Order $order) {
                return '';
            },
            'Line Discount' => function(Order $order) {
                
            },
            'Line Vat',
            'Total Order Discount',
            'Line Total'
        ];
    }

    public function getHeaders($items = true)
    {
        $headers = static::$commonHeaders;
    }

    public function fromOrderAndItems(Order $order, $accountName)
    {
        $orderArray = $order->toArray();
        $line = [];
        $headers = static::$commonHeaders;
        
    }

    public function fromOrder(Order $order, $accountName)
    {

    }
}
