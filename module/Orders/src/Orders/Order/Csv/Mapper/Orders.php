<?php
namespace Orders\Order\Csv\Mapper;

use CG\User\ActiveUserInterface;
use Orders\Order\Csv\MapperInterface;
use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\Mapper\Formatter\StandardSingle as StandardFormatter;
use Orders\Order\Csv\Mapper\Formatter\SalesChannelNameSingle as SalesChannelNameFormatter;
use Orders\Order\Csv\Mapper\Formatter\InvoiceDateSingle as InvoiceDateFormatter;
use CG\OrganisationUnit\Service as OrganisationUnitService;

class Orders implements MapperInterface
{
    protected $standardFormatter;
    protected $salesChannelNameFormatter;
    protected $invoiceDateFormatter;
    /**
     * @var ActiveUserInterface $activeUserContainer
     */
    protected $activeUserContainer;
    /**
     * @var OrganisationUnitService $organisationUnitService
     */
    protected $organisationUnitService;

    public function __construct(
        StandardFormatter $standardFormatter,
        SalesChannelNameFormatter $salesChannelNameFormatter,
        InvoiceDateFormatter $invoiceDateFormatter,
        OrganisationUnitService $organisationUnitService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->setStandardFormatter($standardFormatter)
            ->setSalesChannelNameFormatter($salesChannelNameFormatter)
            ->setInvoiceDateFormatter($invoiceDateFormatter)
            ->setOrganisationUnitService($organisationUnitService)
            ->setActiveUserContainer($activeUserContainer);
    }

    protected function getFormatters()
    {
        $formatters = [
            'Order ID' => 'externalId',
            'Sales Channel Name' => $this->salesChannelNameFormatter,
            'Purchase Date' => 'purchaseDate',
            'Payment Date' => 'paymentDate',
            'Printed Date' => 'printedDate',
            'Dispatch Date' => 'dispatchDate',
            'Invoice Date' => $this->invoiceDateFormatter,
            'Channel' => 'channel',
            'Status' => 'status',
            'Shipping Price' => 'shippingPrice',
            'Shipping Method' => 'shippingMethod',
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
            'Buyer Message' => 'buyerMessage'
        ];
        $rootOrganisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $organisationUnit = $this->organisationUnitService->getRootOuFromOuId($rootOrganisationUnitId);
        if(!$organisationUnit->isVatRegistered()) {
            unset($formatters['Total VAT']);
        }
        return $formatters;
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
                $formatters[$header] = $this->standardFormatter;
            } else {
                $formatters[$header] = $formatter;
            }
        }

        foreach($orderCollection as $order) {
            $row = [];
            foreach ($formatters as $header => $formatter) {
                $row[] = $formatter($order, $columnFormatters[$header]);
            }
            yield [$row];
        }
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
     * @param InvoiceDateFormatter $invoiceDateFormatter
     * @return $this
     */
    public function setInvoiceDateFormatter(InvoiceDateFormatter $invoiceDateFormatter)
    {
        $this->invoiceDateFormatter = $invoiceDateFormatter;
        return $this;
    }

    /**
     * @param OrganisationUnitService $organisationUnitService
     * @return $this
     */
    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @param ActiveUserInterface $activeUserContainer
     * @return $this
     */
    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }
}