<?php
namespace Orders\Order\Csv\Mapper;

use Orders\Order\Csv\MapperInterface;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapMessage as GiftWrapMessageFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapPrice as GiftWrapPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapType as GiftWrapTypeFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingPrice as ShippingPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\SalesChannelName as SalesChannelNameFormatter;
use Orders\Order\Csv\Mapper\Formatter\Standard as StandardFormatter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib;

class OrdersItems implements MapperInterface
{
    protected $giftWrapMessageFormatter;
    protected $giftWrapPriceFormatter;
    protected $giftWrapTypeFormatter;
    protected $shippingPriceFormatter;
    protected $salesChannelNameFormatter;
    protected $standardFormatter;

    public function __construct(
        GiftWrapMessageFormatter $giftWrapMessageFormatter,
        GiftWrapPriceFormatter $giftWrapPriceFormatter,
        GiftWrapTypeFormatter $giftWrapTypeFormatter,
        ShippingPriceFormatter $shippingPriceFormatter,
        SalesChannelNameFormatter $salesChannelNameFormatter,
        StandardFormatter $standardFormatter
    ) {
        $this->setGiftWrapMessageFormatter($giftWrapMessageFormatter)
            ->setGiftWrapPriceFormatter($giftWrapPriceFormatter)
            ->setGiftWrapTypeFormatter($giftWrapTypeFormatter)
            ->setShippingPriceFormatter($shippingPriceFormatter)
            ->setSalesChannelNameFormatter($salesChannelNameFormatter)
            ->setStandardFormatter($standardFormatter);
    }

    protected function getFormatters()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->getSalesChannelNameFormatter(),
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => $this->getShippingPriceFormatter(),
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
            'Gift Wrap Type' => $this->getGiftWrapTypeFormatter(),
            'Gift Wrap Message' => $this->getGiftWrapMessageFormatter(),
            'Gift Wrap Price' => $this->getGiftWrapPriceFormatter()
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
                $formatters[$header] = $this->getStandardFormatter();
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
     * @return GiftWrapMessageFormatter
     */
    protected function getGiftWrapMessageFormatter()
    {
        return $this->giftWrapMessageFormatter;
    }

    /**
     * @param GiftWrapMessageFormatter $giftWrapMessageFormatter
     * @return $this
     */
    public function setGiftWrapMessageFormatter(GiftWrapMessageFormatter $giftWrapMessageFormatter)
    {
        $this->giftWrapMessageFormatter = $giftWrapMessageFormatter;
        return $this;
    }

    /**
     * @return GiftWrapPriceFormatter
     */
    protected function getGiftWrapPriceFormatter()
    {
        return $this->giftWrapPriceFormatter;
    }

    /**
     * @param GiftWrapPriceFormatter $giftWrapPriceFormatter
     * @return $this
     */
    public function setGiftWrapPriceFormatter(GiftWrapPriceFormatter $giftWrapPriceFormatter)
    {
        $this->giftWrapPriceFormatter = $giftWrapPriceFormatter;
        return $this;
    }

    /**
     * @return GiftWrapTypeFormatter
     */
    protected function getGiftWrapTypeFormatter()
    {
        return $this->giftWrapTypeFormatter;
    }

    /**
     * @param GiftWrapTypeFormatter $giftWrapTypeFormatter
     * @return $this
     */
    public function setGiftWrapTypeFormatter(GiftWrapTypeFormatter $giftWrapTypeFormatter)
    {
        $this->giftWrapTypeFormatter = $giftWrapTypeFormatter;
        return $this;
    }

    /**
     * @return ShippingPriceFormatter
     */
    protected function getShippingPriceFormatter()
    {
        return $this->shippingPriceFormatter;
    }

    /**
     * @param ShippingPriceFormatter $shippingPriceFormatter
     * @return $this
     */
    public function setShippingPriceFormatter(ShippingPriceFormatter $shippingPriceFormatter)
    {
        $this->shippingPriceFormatter = $shippingPriceFormatter;
        return $this;
    }

    /**
     * @return SalesChannelNameFormatter
     */
    protected function getSalesChannelNameFormatter()
    {
        return $this->salesChannelNameFormatter;
    }

    /**
     * @param SalesChannelNameFormatter $salesChannelNameFormatter
     * @return $this
     */
    public function setSalesChannelNameFormatter(SalesChannelNameFormatter $salesChannelNameFormatter)
    {
        $this->salesChannelNameFormatter = $salesChannelNameFormatter;
        return $this;
    }

    /**
     * @return StandardFormatter
     */
    protected function getStandardFormatter()
    {
        return $this->standardFormatter;
    }

    /**
     * @param StandardFormatter $standardFormatter
     * @return $this
     */
    public function setStandardFormatter(StandardFormatter $standardFormatter)
    {
        $this->standardFormatter = $standardFormatter;
        return $this;
    }
}
