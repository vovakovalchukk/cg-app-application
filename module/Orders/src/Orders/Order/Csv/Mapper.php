<?php
namespace Orders\Order\Csv;

use Orders\Order\Csv\Formatters\GiftWrapMessage;
use Orders\Order\Csv\Formatters\GiftWrapPrice;
use Orders\Order\Csv\Formatters\GiftWrapType;
use Orders\Order\Csv\Formatters\LineDiscount;
use Orders\Order\Csv\Formatters\LineTotal;
use Orders\Order\Csv\Formatters\ShippingPrice;
use Orders\Order\Csv\Formatters\Standard;
use Orders\Order\Csv\Formatters\TotalOrderDiscount;
use Orders\Order\Csv\Formatters\TotalOrderDiscountSingle;
use Orders\Order\Csv\Formatters\SalesChannelName;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib;
use Zend\Di\Di;

class Mapper
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    protected function getOrderColumns()
    {
        //TODO: CGIV-5377
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' =>  SalesChannelName::class,
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => 'shippingPrice',
            'Shipping Method' => 'shippingMethod',
            'Currency Code' => 'currencyCode',
            'Subtotal' => 'subtotal',
            'Total VAT' => '',
            'Total Discount' => TotalOrderDiscountSingle::class,
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

    protected function getOrderAndItemsColumns()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => SalesChannelName::class,
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => ShippingPrice::class,
            'Shipping Method' => 'shippingMethod',
            'Currency Code' => 'currencyCode',
            'Item Name' => 'itemName',
            'Unit Price' => 'individualItemPrice',
            'Quantity' => 'itemQuantity',
            'SKU' => 'itemSku',
            'VAT %' => '',
            'Line Discount' => LineDiscount::class,
            'Line Vat' => '',
            'Total Order Discount' => TotalOrderDiscount::class,
            'Line Total' => LineTotal::class,
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
            'Gift Wrap Type' => GiftWrapType::class,
            'Gift Wrap Message' => GiftWrapMessage::class,
            'Gift Wrap Price' => GiftWrapPrice::class
        ];
    }

    public function getOrderAndItemsHeaders()
    {
        return array_keys($this->getOrderAndItemsColumns());
    }

    public function getOrderHeaders()
    {
        return array_keys($this->getOrderColumns());
    }

    public function fromOrderCollection(OrderCollection $orderCollection)
    {
        $columnFormatters = $this->getOrderAndItemsColumns();
        $columns = [];
        $standardFormattedColumns = [];
        foreach($columnFormatters as $header => $formatterName) {
            if(class_exists($formatterName)) {
                $formatter = $this->getDi()->get($formatterName);
                $columns[$header] = $formatter($orderCollection);
            } else {
                $columns[$header] = [];
                $standardFormattedColumns[$header] = $formatterName;
            }
        }
        $formatter = new Standard($standardFormattedColumns);
        $columns = array_merge($columns, $formatter($orderCollection));
        $rows = Stdlib\transposeArray($columns);
        return $rows;
    }

    /**
     * @return Di
     */
    protected function getDi()
    {
        return $this->di;
    }

    /**
     * @param Di $di
     * @return $this
     */
    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }
}
