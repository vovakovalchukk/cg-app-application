<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Entity as Item;

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

    public function getOrderOnlyHeaders()
    {
        return [
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
            'Subtotal' => function(OrderCollection $orders) {
                $column = [];
                foreach($orders as $order) {
                    $column[] = $order->getSubtotal();
                }
                return $column;
            },
            'Total VAT' => '',
            'Total Discount' => 'totalDiscount',
            'Total' => 'total',
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
    }

    public function getOrderAndItemsHeaders()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => 'accountId',
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => function(Order $order, Item $item) {
                if($order->getItems()->offsetGet($item) > 1) {
                    return '';
                }
                return $order->getShippingPrice();
            },
            'Shipping Method' => 'shippingMethod',
            'Currency Code' => 'currencyCode',
            'Item Name' => 'itemName',
            'Unit Price' => 'individualItemPrice',
            'Quantity' => 'itemQuantity',
            'SKU' => 'itemSku',
            'VAT %' => '',
            'Line Discount' => function(Order $order, Item $item) {
                return (float) $item->getItemQuantity() * (float) $item->getIndividualItemDiscountPrice();
            },
            'Line Vat' => '',
            'Total Order Discount' => function(Order $order, Item $item) {
                $totalDiscount = $order->getTotalDiscount();
                if($order->getItems()->count() === 0) {
                    return $totalDiscount;
                }
                /** @var Item $item */
                foreach($order->getItems() as $item) {
                    $totalDiscount += $item->getIndividualItemDiscountPrice() * $item->getItemQuantity();
                }
                return $totalDiscount;
            },
            'Line Total' => function(Order $order, Item $item) {
                return (float) $item->getItemQuantity() * (float) $item->getIndividualItemPrice();
            },
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
            'Gift Wrap Type' => function(Order $order, Item $item) {
                if($item->getGiftWraps() == null || $item->getGiftWraps()->count() === 0) {
                    return '';
                }
                $item->getGiftWraps()->rewind();
                return $item->getGiftWraps()->current()->getGiftWrapType();
            },
            'Gift Wrap Message' => function(Order $order, Item $item) {
                if($item->getGiftWraps() == null || $item->getGiftWraps()->count() === 0) {
                    return '';
                }
                $item->getGiftWraps()->rewind();
                return $item->getGiftWraps()->current()->getGiftWrapMessage();
            },
            'Gift Wrap Price' => function(Order $order, Item $item) {
                if($item->getGiftWraps() == null || $item->getGiftWraps()->count() === 0) {
                    return '';
                }
                $item->getGiftWraps()->rewind();
                return $item->getGiftWraps()->current()->getGiftWrapPrice();
            }
        ];
    }

    public function getOrderAndItemsHeadersNames()
    {
        return array_keys($this->getOrderAndItemsHeaders());
    }

    public function fromOrderAndItems(Order $order, $accountName)
    {
        $headers = $this->getOrderAndItemsHeaders();
        $orderArray = $order->toArray();
        $lines = [];
        $items = $order->getItems();
        if($items != null && $items->count() !== 0) {
            foreach($items as $item) {
                $itemArray = $item->toArray();
                $line = [];
                foreach($headers as $headerName => $formatter) {
                    if(is_string($formatter)) {
                        if(isset($orderArray[$formatter])) {
                            $line[] = $orderArray[$formatter];
                        } elseif(isset($itemArray[$formatter])) {
                            $line[] = $itemArray[$formatter];
                        } else {
                            $line[] = '';
                        }
                    } elseif(is_callable($formatter)) {
                        $line[] = $formatter($order, $item);
                    }
                }
                $lines[] = $line;
            }
        }
        return $lines;
    }

    public function fromOrder(Order $order, $accountName)
    {

    }
}
