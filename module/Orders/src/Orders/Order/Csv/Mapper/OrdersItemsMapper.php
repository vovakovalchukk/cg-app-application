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

class OrdersItemsMapper
{
    protected $giftWrapMessage;
    protected $giftWrapPrice;
    protected $giftWrapType;
    protected $shippingPrice;
    protected $standard;
    protected $salesChannelName;

    public function __construct(
        GiftWrapMessage $giftWrapMessage,
        GiftWrapPrice $giftWrapPrice,
        GiftWrapType $giftWrapType,
        ShippingPrice $shippingPrice,
        Standard $standard,
        SalesChannelName $salesChannelName
    ) {
        $this->setGiftWrapMessage($giftWrapMessage)
            ->setGiftWrapPrice($giftWrapPrice)
            ->setGiftWrapType($giftWrapType)
            ->setShippingPrice($shippingPrice)
            ->setStandard($standard)
            ->setSalesChannelName($salesChannelName);
    }

    protected function getFormatters()
    {
        return [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->getSalesChannelName(),
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => $this->getShippingPrice(),
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
            'Gift Wrap Type' => $this->getGiftWrapType(),
            'Gift Wrap Message' => $this->getGiftWrapMessage(),
            'Gift Wrap Price' => $this->getGiftWrapPrice()
        ];
    }

    public function getHeaders()
    {
        return array_keys($this->getFormatters());
    }

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
            $columns = [];
            foreach ($formatters as $header => $formatter) {
                $columns[] = $formatter($order, $columnFormatters[$header]);
            }
            yield Stdlib\transposeArray($columns);
        }
    }

    /**
     * @return GiftWrapMessage
     */
    protected function getGiftWrapMessage()
    {
        return $this->giftWrapMessage;
    }

    /**
     * @param GiftWrapMessage $giftWrapMessage
     * @return $this
     */
    public function setGiftWrapMessage(GiftWrapMessage $giftWrapMessage)
    {
        $this->giftWrapMessage = $giftWrapMessage;
        return $this;
    }

    /**
     * @return GiftWrapPrice
     */
    protected function getGiftWrapPrice()
    {
        return $this->giftWrapPrice;
    }

    /**
     * @param GiftWrapPrice $giftWrapPrice
     * @return $this
     */
    public function setGiftWrapPrice(GiftWrapPrice $giftWrapPrice)
    {
        $this->giftWrapPrice = $giftWrapPrice;
        return $this;
    }

    /**
     * @return GiftWrapType
     */
    protected function getGiftWrapType()
    {
        return $this->giftWrapType;
    }

    /**
     * @param GiftWrapType $giftWrapType
     * @return $this
     */
    public function setGiftWrapType(GiftWrapType $giftWrapType)
    {
        $this->giftWrapType = $giftWrapType;
        return $this;
    }

    /**
     * @return ShippingPrice
     */
    protected function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * @param ShippingPrice $shippingPrice
     * @return $this
     */
    public function setShippingPrice(ShippingPrice $shippingPrice)
    {
        $this->shippingPrice = $shippingPrice;
        return $this;
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

    /**
     * @return SalesChannelName
     */
    protected function getSalesChannelName()
    {
        return $this->salesChannelName;
    }

    /**
     * @param SalesChannelName $salesChannelName
     * @return $this
     */
    public function setSalesChannelName(SalesChannelName $salesChannelName)
    {
        $this->salesChannelName = $salesChannelName;
        return $this;
    }
}
