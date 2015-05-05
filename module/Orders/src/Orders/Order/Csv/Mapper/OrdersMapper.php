<?php
namespace Orders\Order\Csv\Mapper;

use Orders\Order\Csv\MapperInterface;
use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\Formatters\StandardSingle as Standard;

class Orders implements MapperInterface
{
    protected $standard;

    public function __construct(Standard $standard)
    {
        $this->setStandard($standard);
    }

    protected function getFormatters()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => 'accountId',//$this->getSalesChannelName(),
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

    /**
     * @return array
     */
    public function getHeaders()
    {
        return array_keys($this->getFormatters());
    }

    /**
     * @param OrderCollection $orderCollection
     * @return \Generator
     */
    public function fromOrderCollection(OrderCollection $orderCollection)
    {
        $columnFormatters = $this->getFormatters();
        $formatters = [];
        foreach($columnFormatters as $header => $formatter) {
            if(!is_object($formatter)) {
                $formatters[$header] = $this->getStandard();
            } else {
                $formatters[$header] = $formatter;
            }
        }

        foreach($orderCollection as $order) {
            $row = [];
            foreach ($formatters as $header => $formatter) {
                $row[] = $formatter($order, $columnFormatters[$header]);
            }
            yield $row;
        }
    }

    /**
     * @return Standard
     */
    protected function getStandard()
    {
        return $this->standard;
    }

    /**
     * @param Standard $standard
     * @return $this
     */
    public function setStandard(Standard $standard)
    {
        $this->standard = $standard;
        return $this;
    }
}