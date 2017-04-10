<?php
namespace Orders\Order\Csv\Mapper;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter\StorageInterface as OrderFilterStorage;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib;
use CG\User\ActiveUserInterface;
use Orders\Order\Csv\Mapper\Formatter\Date as DateFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapMessage as GiftWrapMessageFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapPrice as GiftWrapPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\GiftWrapType as GiftWrapTypeFormatter;
use Orders\Order\Csv\Mapper\Formatter\InvoiceDate as InvoiceDateFormatter;
use Orders\Order\Csv\Mapper\Formatter\SalesChannelName as SalesChannelNameFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingMethod as ShippingMethodFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingPrice as ShippingPriceFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingVat as ShippingVatFormatter;
use Orders\Order\Csv\Mapper\Formatter\Standard as StandardFormatter;
use Orders\Order\Csv\Mapper\Formatter\VatRate as VatRateFormatter;
use Orders\Order\Csv\Mapper\Formatter\VatNumber as VatNumberFormatter;
use Orders\Order\Csv\MapperInterface;

class OrdersItems implements MapperInterface
{
    const ORDERS_PER_PAGE = 500;

    /** @var OrderService $orderService */
    protected $orderService;
    /** @var OrderFilterStorage $orderFilterStorage */
    protected $orderFilterStorage;
    /** @var GiftWrapMessageFormatter $giftWrapMessageFormatter */
    protected $giftWrapMessageFormatter;
    /** @var GiftWrapPriceFormatter $giftWrapPriceFormatter */
    protected $giftWrapPriceFormatter;
    /** @var GiftWrapTypeFormatter $giftWrapTypeFormatter */
    protected $giftWrapTypeFormatter;
    /** @var ShippingPriceFormatter $shippingPriceFormatter */
    protected $shippingPriceFormatter;
    /** @var ShippingVatFormatter $shippingVatFormatter */
    protected $shippingVatFormatter;
    /** @var ShippingMethodFormatter $shippingMethodFormatter */
    protected $shippingMethodFormatter;
    /** @var SalesChannelNameFormatter $salesChannelNameFormatter */
    protected $salesChannelNameFormatter;
    /** @var VatRateFormatter $vatRateFormatter */
    protected $vatRateFormatter;
    /** @var StandardFormatter $standardFormatter */
    protected $standardFormatter;
    /** @var DateFormatter $dateFormatter */
    protected $dateFormatter;
    /** @var InvoiceDateFormatter $invoiceDateFormatter */
    protected $invoiceDateFormatter;
    /** @var VatNumberFormatter */
    protected $vatNumberFormatter;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        OrderService $orderService,
        OrderFilterStorage $orderFilterStorage,
        GiftWrapMessageFormatter $giftWrapMessageFormatter,
        GiftWrapPriceFormatter $giftWrapPriceFormatter,
        GiftWrapTypeFormatter $giftWrapTypeFormatter,
        ShippingPriceFormatter $shippingPriceFormatter,
        ShippingVatFormatter $shippingVatFormatter,
        ShippingMethodFormatter $shippingMethodFormatter,
        SalesChannelNameFormatter $salesChannelNameFormatter,
        VatRateFormatter $vatRateFormatter,
        StandardFormatter $standardFormatter,
        DateFormatter $dateFormatter,
        InvoiceDateFormatter $invoiceDateFormatter,
        VatNumberFormatter $vatNumberFormatter,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->orderService = $orderService;
        $this->orderFilterStorage = $orderFilterStorage;
        $this->giftWrapMessageFormatter = $giftWrapMessageFormatter;
        $this->giftWrapPriceFormatter = $giftWrapPriceFormatter;
        $this->giftWrapTypeFormatter = $giftWrapTypeFormatter;
        $this->shippingPriceFormatter = $shippingPriceFormatter;
        $this->shippingVatFormatter = $shippingVatFormatter;
        $this->shippingMethodFormatter = $shippingMethodFormatter;
        $this->salesChannelNameFormatter = $salesChannelNameFormatter;
        $this->vatRateFormatter = $vatRateFormatter;
        $this->standardFormatter = $standardFormatter;
        $this->dateFormatter = $dateFormatter;
        $this->invoiceDateFormatter = $invoiceDateFormatter;
        $this->vatNumberFormatter = $vatNumberFormatter;
        $this->activeUserContainer = $activeUserContainer;
        $this->organisationUnitService = $organisationUnitService;
    }

    protected function getFormatters()
    {
        $formatters =  [
            'Order ID' => 'externalId',
            'Order Item ID' => ['item', 'externalId'],
            'Sales Channel Name' => $this->salesChannelNameFormatter,
            'Purchase Date' => ['field' => 'purchaseDate', 'formatter' => $this->dateFormatter],
            'Payment Date' => ['field' => 'paymentDate', 'formatter' => $this->dateFormatter],
            'Printed Date' => ['field' => 'printedDate', 'formatter' => $this->dateFormatter],
            'Dispatch Date' => ['field' => 'dispatchDate', 'formatter' => $this->dateFormatter],
            'Invoice Date' => ['field' => 'invoiceDate', 'formatter' => $this->invoiceDateFormatter],
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => $this->shippingPriceFormatter,
            'Shipping Method' => $this->shippingMethodFormatter,
            'Currency Code' => 'currencyCode',
            'Item Name' => 'itemName',
            'Unit Price' => 'individualItemPriceString',
            'Quantity' => 'itemQuantity',
            'SKU' => 'itemSku',
            'VAT %' => $this->vatRateFormatter,
            'Line Discount' => 'lineDiscountString',
            'Line VAT' => 'lineTaxString',
            'Total Order Discount' => 'totalOrderAndItemsDiscountString',
            'Line Total' => 'lineTotalString',
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
            'Gift Wrap Price' => $this->giftWrapPriceFormatter,
            'Invoice Number' => 'invoiceNumber',
            'VAT Number' => $this->vatNumberFormatter,
            'Billing Username' => 'externalUsername',
            'Shipping VAT' => $this->shippingVatFormatter,
        ];

        $rootOrganisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $organisationUnit = $this->organisationUnitService->getRootOuFromOuId($rootOrganisationUnitId);
        if(!$organisationUnit->isVatRegistered()) {
            unset($formatters['VAT %'], $formatters['Line VAT'], $formatters['VAT Number']);
        }
        return $formatters;
    }

    /**
     * @inherit
     */
    public function getHeaders()
    {
        return array_keys($this->getFormatters());
    }

    /**
     * @inherit
     */
    public function fromOrderFilter(OrderFilter $orderFilter)
    {
        /** @var OrderFilter $orderFilter */
        $orderFilter = $this->orderFilterStorage->save($orderFilter->setConvertToOrderIds(true));

        $page = 1;
        do {
            /** @var OrderCollection $orderCollection */
            $orderCollection = $this->orderService->fetchCollectionByFilter(
                $orderFilter->setLimit(static::ORDERS_PER_PAGE)->setPage($page)
            );

            foreach ($this->fromOrderCollection($orderCollection) as $rows) {
                yield $rows;
            }
        } while (($page++ * static::ORDERS_PER_PAGE) < $orderCollection->getTotal());
    }

    /**
     * @inherit
     */
    public function fromOrderCollection(OrderCollection $orderCollection)
    {
        $columnFormatters = $this->getFormatters();
        $formatters = [];
        foreach ($columnFormatters as $header => $formatter) {
            if(is_object($formatter)) {
                $formatters[$header] = $formatter;
                $fieldNames[$header] = '';
            } elseif (is_array($formatter) && isset($formatter['formatter'], $formatter['field'])) {
                $formatters[$header] = $formatter['formatter'];
                $fieldNames[$header] = $formatter['field'];
            } else {
                $formatters[$header] = $this->standardFormatter;
                $fieldNames[$header] = $formatter;
            }
        }

        foreach ($orderCollection as $order) {
            $columns = [];
            foreach ($formatters as $header => $formatter) {
                $columns[] = $formatter($order, $fieldNames[$header]);
            }
            yield Stdlib\transposeArray($columns);
        }
    }
}
