<?php
namespace Orders\Order\Csv\Mapper;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter\StorageInterface as OrderFilterStorage;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\ActiveUserInterface;
use Orders\Order\Csv\Mapper\Formatter\AlertSingle as AlertFormatter;
use Orders\Order\Csv\Mapper\Formatter\DateSingle as DateFormatter;
use Orders\Order\Csv\Mapper\Formatter\InvoiceDateSingle as InvoiceDateFormatter;
use Orders\Order\Csv\Mapper\Formatter\SalesChannelNameSingle as SalesChannelNameFormatter;
use Orders\Order\Csv\Mapper\Formatter\ShippingMethodSingle as ShippingMethodFormatter;
use Orders\Order\Csv\Mapper\Formatter\StandardSingle as StandardFormatter;
use Orders\Order\Csv\Mapper\Formatter\VatNumberSingle as VatNumberFormatter;
use Orders\Order\Csv\MapperInterface;

class Orders implements MapperInterface
{
    const ORDERS_PER_PAGE = 500;

    /** @var OrderService $orderService */
    protected $orderService;
    /** @var OrderFilterStorage $orderFilterStorage */
    protected $orderFilterStorage;
    /** @var StandardFormatter $standardFormatter */
    protected $standardFormatter;
    /** @var SalesChannelNameFormatter $salesChannelNameFormatter */
    protected $salesChannelNameFormatter;
    /** @var ShippingMethodFormatter $shippingMethodFormatter */
    protected $shippingMethodFormatter;
    /** @var DateFormatter $dateFormatter */
    protected $dateFormatter;
    /** @var InvoiceDateFormatter $invoiceDateFormatter */
    protected $invoiceDateFormatter;
    /** @var VatNumberFormatter */
    protected $vatNumberFormatter;
    /** @var AlertFormatter */
    protected $alertFormatter;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        OrderService $orderService,
        OrderFilterStorage $orderFilterStorage,
        StandardFormatter $standardFormatter,
        SalesChannelNameFormatter $salesChannelNameFormatter,
        ShippingMethodFormatter $shippingMethodFormatter,
        DateFormatter $dateFormatter,
        InvoiceDateFormatter $invoiceDateFormatter,
        VatNumberFormatter $vatNumberFormatter,
        AlertFormatter $alertFormatter,
        OrganisationUnitService $organisationUnitService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->orderService = $orderService;
        $this->orderFilterStorage = $orderFilterStorage;
        $this->standardFormatter = $standardFormatter;
        $this->salesChannelNameFormatter = $salesChannelNameFormatter;
        $this->shippingMethodFormatter = $shippingMethodFormatter;
        $this->dateFormatter = $dateFormatter;
        $this->invoiceDateFormatter = $invoiceDateFormatter;
        $this->vatNumberFormatter = $vatNumberFormatter;
        $this->alertFormatter = $alertFormatter;
        $this->organisationUnitService = $organisationUnitService;
        $this->activeUserContainer = $activeUserContainer;
    }

    protected function getFormatters()
    {
        $formatters = [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->salesChannelNameFormatter,
            'Purchase Date' => ['field' => 'purchaseDate', 'formatter' => $this->dateFormatter],
            'Payment Date' => ['field' => 'paymentDate', 'formatter' => $this->dateFormatter],
            'Printed Date' => ['field' => 'printedDate', 'formatter' => $this->dateFormatter],
            'Dispatch Date' => ['field' => 'dispatchDate', 'formatter' => $this->dateFormatter],
            'Invoice Date' => ['field' => 'invoiceDate', 'formatter' => $this->invoiceDateFormatter],
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => 'shippingPrice',
            'Shipping Method' => $this->shippingMethodFormatter,
            'Currency Code' => 'currencyCode',
            'Subtotal' => 'subtotal',
            'Total VAT' => 'tax',
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
            'Buyer Message' => 'buyerMessage',
            'Invoice Number' => 'invoiceNumber',
            'VAT Number' => $this->vatNumberFormatter,
            'Billing Username' => 'externalUsername',
            'Shipping VAT' => 'shippingTaxString',
            'Order Alert' => $this->alertFormatter,
        ];
        $rootOrganisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $organisationUnit = $this->organisationUnitService->getRootOuFromOuId($rootOrganisationUnitId);
        if(!$organisationUnit->isVatRegistered()) {
            unset($formatters['Total VAT'], $formatters['VAT Number']);
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
            $orderCollection = $this->orderService->fetchCollectionIncludingLinkedOrdersByFilter(
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
        $fieldNames = [];
        foreach($columnFormatters as $header => $formatter) {
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

        foreach($orderCollection as $order) {
            $row = [];
            foreach ($formatters as $header => $formatter) {
                $row[] = $formatter($order, $fieldNames[$header]); 
            }
            yield [$row];
        }
    }
}
