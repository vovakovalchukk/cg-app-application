<?php
namespace DataExchange\Template;

use CG\DataExchangeTemplate\Entity as Template;
use CG\DataExchangeTemplate\Filter as TemplateFilter;
use CG\DataExchangeTemplate\Mapper as TemplateMapper;
use CG\DataExchangeTemplate\Service as TemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    const CG_FIELDS_STOCK = [
        'SKU' => 'sku',
        'Product Name' => 'name',
        'Total Stock' => 'quantity',
        'Cost Price' => 'costPrice'
    ];

    const CG_FIELDS_ORDERS = [
        'Order ID' => 'externalId',
        'Order Item ID' => 'item.externalId',
        'Sales Channel Name' => 'channel',
        'Purchase Date' => 'purchaseDate',
        'Payment Date' => 'paymentDate',
        'Printed Date' => 'printedDate',
        'Dispatch Date' => 'dispatchDate',
        'Invoice Date' => 'invoiceDate',
        'Channel' => 'channel',
        'Status' => 'status',
        'Shipping Price' => 'shippingPrice',
        'Shipping Method' => 'shippingMethod',
        'Currency Code' => 'currencyCode',
        'Item Name' => 'item.itemName',
        'Unit Price' => 'item.individualItemPrice',
        'Quantity' => 'item.itemQuantity',
        'SKU' => 'item.itemSku',
        'Line Discount' => 'item.individualItemDiscountPrice',
        'Line VAT' => 'item.itemTaxPercentage',
        'Total Order Discount' => 'totalDiscount',
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
        'Invoice Number' => 'invoiceNumber',
        'VAT Number' => 'vatNumber',
        'Billing Username' => 'externalUsername',
    ];

    const CG_FIELDS_MAP_BY_TYPE = [
        Template::TYPE_STOCK => self::CG_FIELDS_STOCK,
        Template::TYPE_ORDER => self::CG_FIELDS_ORDERS,
    ];

    /** @var TemplateService */
    protected $templateService;
    /** @var TemplateMapper */
    protected $templateMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        TemplateService $templateService,
        TemplateMapper $templateMapper,
        ActiveUserInterface $activeUserContainer
    ){
        $this->templateService = $templateService;
        $this->templateMapper = $templateMapper;
        $this->activeUserContainer = $activeUserContainer;
    }

    public static function getCgFieldOptionsByType(string $type): array
    {
        return self::CG_FIELDS_MAP_BY_TYPE[$type];
    }

    public function fetchAllTemplatesForActiveUser(string $type): array
    {
        try {
            $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildTemplateFilter($ouId, $type);
            $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
            $templatesArray = [];
            /** @var Template $template */
            foreach ($templateCollection as $template) {
                $templatesArray[] = $template->toArray();
            }
            return $templatesArray;
        } catch (NotFound $exception) {
            return [];
        }
    }

    protected function buildTemplateFilter(int $ouId, string $type): TemplateFilter
    {
        return (new TemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType([$type])
            ->setOrganisationUnitId([$ouId]);
    }

    public function saveForActiveUser(string $type, array $templateArray, ?int $templateId = null): Template
    {
        if (!$templateId) {
            return $this->saveNewTemplate($type, $templateArray);
        }

        return $this->updateExistingTemplate($type, $templateArray, $templateId);
    }

    public function remove(string $type, int $id): void
    {
        $template = $this->fetchTemplateByTypeAndId($type, $id);
        $this->templateService->remove($template);
    }

    protected function fetchTemplateByTypeAndId(string $type, int $id): Template
    {
        $filter = (new TemplateFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setType([$type])
            ->setId([$id]);

        $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
        return $templateCollection->getFirst();
    }

    protected function saveNewTemplate(string $type, array $templateArray): Template
    {
        $templateArray = array_merge($templateArray, [
            'type' => $type,
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        ]);

        $template = $this->templateMapper->fromArray($templateArray);
        $this->templateService->save($template);
        return $this->templateService->fetch($template->getId());
    }

    protected function updateExistingTemplate(string $type, array $templateArray, int $templateId): Template
    {
        /** @var Template $existingTemplate */
        $existingTemplate = $this->fetchTemplateByTypeAndId($type, $templateId);
        $updatedTemplate = $this->templateMapper->fromArray(
            array_merge($existingTemplate->toArray(), $templateArray)
        );
        $updatedTemplate->setStoredETag($templateArray['etag'] ?? $existingTemplate->getETag());
        $this->templateService->save($updatedTemplate);
        return $this->templateService->fetch($updatedTemplate->getId());
    }
}
