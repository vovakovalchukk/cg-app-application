<?php
namespace Orders\Order\Csv\Mapper;

use Orders\Order\Csv\MapperInterface;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapMessage as GiftWrapMessageFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapPrice as GiftWrapPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapType as GiftWrapTypeFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingPrice as ShippingPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingMethod as ShippingMethodFormatter;
use Orders\Order\Csv\Mapper\Formatter\SalesChannelName as SalesChannelNameFormatter;
use Orders\Order\Csv\Mapper\Formatter\InvoiceDate as InvoiceDateFormatter;
use Orders\Order\Csv\Mapper\Formatter\VatRate as VatRateFormatter;
use Orders\Order\Csv\Mapper\Formatter\Standard as StandardFormatter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib;

class OrdersItems implements MapperInterface
{
    protected $giftWrapMessageFormatter;
    protected $giftWrapPriceFormatter;
    protected $giftWrapTypeFormatter;
    protected $shippingPriceFormatter;
    protected $shippingMethodFormatter;
    protected $salesChannelNameFormatter;
    protected $invoiceDateFormatter;
    protected $vatRateFormatter;
    protected $standardFormatter;

    public function __construct(
        GiftWrapMessageFormatter $giftWrapMessageFormatter,
        GiftWrapPriceFormatter $giftWrapPriceFormatter,
        GiftWrapTypeFormatter $giftWrapTypeFormatter,
        ShippingPriceFormatter $shippingPriceFormatter,
        ShippingMethodFormatter $shippingMethodFormatter,
        SalesChannelNameFormatter $salesChannelNameFormatter,
        InvoiceDateFormatter $invoiceDateFormatter,
        VatRateFormatter $vatRateFormatter,
        StandardFormatter $standardFormatter
    ) {
        $this
            ->setGiftWrapMessageFormatter($giftWrapMessageFormatter)
            ->setGiftWrapPriceFormatter($giftWrapPriceFormatter)
            ->setGiftWrapTypeFormatter($giftWrapTypeFormatter)
            ->setShippingPriceFormatter($shippingPriceFormatter)
            ->setShippingMethodFormatter($shippingMethodFormatter)
            ->setSalesChannelNameFormatter($salesChannelNameFormatter)
            ->setInvoiceDateFormatter($invoiceDateFormatter)
            ->setVatRateFormatter($vatRateFormatter)
            ->setStandardFormatter($standardFormatter);
    }

    protected function getFormatters()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->salesChannelNameFormatter,
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Invoice Date' => $this->invoiceDateFormatter,
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => $this->shippingPriceFormatter,
            'Shipping Method' => $this->shippingMethodFormatter,
            'Currency Code' => 'currencyCode',
            'Item Name' => 'itemName',
            'Unit Price' => 'individualItemPrice',
            'Quantity' => 'itemQuantity',
            'SKU' => 'itemSku',
            'VAT %' => $this->vatRateFormatter,
            'Line Discount' => 'lineDiscount',
            'Line VAT' => 'lineTax',
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
            'Gift Wrap Type' => $this->giftWrapTypeFormatter,
            'Gift Wrap Message' => $this->giftWrapMessageFormatter,
            'Gift Wrap Price' => $this->giftWrapPriceFormatter
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
        foreach ($columnFormatters as $header => $formatter) {
            if (!is_object($formatter)) {
                $formatters[$header] = $this->standardFormatter;
            } else {
                $formatters[$header] = $formatter;
            }
        }

        foreach ($orderCollection as $order) {
            $columns = [];
            foreach ($formatters as $header => $formatter) {
                $columns[] = $formatter($order, $columnFormatters[$header]);
            }
            yield Stdlib\transposeArray($columns);
        }
    }

    /**
     * @param GiftWrapMessageFormatter $giftWrapMessageFormatter
     * @return self
     */
    public function setGiftWrapMessageFormatter(GiftWrapMessageFormatter $giftWrapMessageFormatter)
    {
        $this->giftWrapMessageFormatter = $giftWrapMessageFormatter;
        return $this;
    }

    /**
     * @param GiftWrapPriceFormatter $giftWrapPriceFormatter
     * @return self
     */
    public function setGiftWrapPriceFormatter(GiftWrapPriceFormatter $giftWrapPriceFormatter)
    {
        $this->giftWrapPriceFormatter = $giftWrapPriceFormatter;
        return $this;
    }

    /**
     * @param GiftWrapTypeFormatter $giftWrapTypeFormatter
     * @return self
     */
    public function setGiftWrapTypeFormatter(GiftWrapTypeFormatter $giftWrapTypeFormatter)
    {
        $this->giftWrapTypeFormatter = $giftWrapTypeFormatter;
        return $this;
    }

    /**
     * @param ShippingPriceFormatter $shippingPriceFormatter
     * @return self
     */
    public function setShippingPriceFormatter(ShippingPriceFormatter $shippingPriceFormatter)
    {
        $this->shippingPriceFormatter = $shippingPriceFormatter;
        return $this;
    }

    /**
     * @param ShippingMethodFormatter $shippingMethodFormatter
     * @return self
     */
    public function setShippingMethodFormatter(ShippingMethodFormatter $shippingMethodFormatter)
    {
        $this->shippingMethodFormatter = $shippingMethodFormatter;
        return $this;
    }

    /**
     * @param SalesChannelNameFormatter $salesChannelNameFormatter
     * @return self
     */
    public function setSalesChannelNameFormatter(SalesChannelNameFormatter $salesChannelNameFormatter)
    {
        $this->salesChannelNameFormatter = $salesChannelNameFormatter;
        return $this;
    }

    /**
     * @param InvoiceDateFormatter $invoiceDateFormatter
     * @return self
     */
    public function setInvoiceDateFormatter(InvoiceDateFormatter $invoiceDateFormatter)
    {
        $this->invoiceDateFormatter = $invoiceDateFormatter;
        return $this;
    }

    /**
     * @param VatRateFormatter $vatRateFormatter
     * @return self
     */
    public function setVatRateFormatter(VatRateFormatter $vatRateFormatter)
    {
        $this->vatRateFormatter = $vatRateFormatter;
        return $this;
    }

    /**
     * @param StandardFormatter $standardFormatter
     * @return self
     */
    public function setStandardFormatter(StandardFormatter $standardFormatter)
    {
        $this->standardFormatter = $standardFormatter;
        return $this;
    }
}
