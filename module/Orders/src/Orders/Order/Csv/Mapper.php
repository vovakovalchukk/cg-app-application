<?php
namespace Orders\Order\Csv;

use Orders\Order\Csv\Formatters\GiftWrapMessage;
use Orders\Order\Csv\Formatters\GiftWrapPrice;
use Orders\Order\Csv\Formatters\GiftWrapType;
use Orders\Order\Csv\Formatters\ShippingPrice;
use Orders\Order\Csv\Formatters\Standard;
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
            'Sales Channel Name' => $this->getDi()->get(SalesChannelName::class),
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
            'Total Discount' => 'totalOrderAndItemsDiscount',
            'Total' => 'total',
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
            'Buyer Message' => 'buyerMessage'
        ];
    }

    protected function getOrderAndItemsColumns()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->getDi()->get(SalesChannelName::class),
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => $this->getDi()->get(ShippingPrice::class),
            'Shipping Method' => 'shippingMethod',
            'Currency Code' => 'currencyCode',
            'Item Name' => 'itemName',
            'Unit Price' => 'individualItemPrice',
            'Quantity' => 'itemQuantity',
            'SKU' => 'itemSku',
            'VAT %' => '',
            'Line Discount' => 'lineDiscount',
            'Line Vat' => '',
            'Total Order Discount' => 'totalOrderAndItemsDiscount',
            'Line Total' => 'lineTotal',
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
            'Gift Wrap Type' => $this->getDi()->get(GiftWrapType::class),
            'Gift Wrap Message' => $this->getDi()->get(GiftWrapMessage::class),
            'Gift Wrap Price' => $this->getDi()->get(GiftWrapPrice::class)
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
        $formatters = [];
        foreach($columnFormatters as $header => $formatter) {
            if(!is_object($formatter)) {
                $formatters[$header] = $this->getDi()->newInstance(Standard::class, ['fieldName' => $formatter]);
            } else {
                $formatters[$header] = $formatter;
            }
        }

        foreach($orderCollection as $order) {
            $columns = [];
            foreach ($formatters as $header => $formatter) {
                $columns[] = $formatter($order);
            }
            yield Stdlib\transposeArray($columns);
        }
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
