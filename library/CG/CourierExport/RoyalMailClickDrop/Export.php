<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use function CG\Stdlib\str_putcsv;

class Export implements ExportDocumentInterface
{
    protected $headers = [
        'Order reference' => 'orderReference',
        'Special instructions' => 'specialInstructions',
        'Date' => 'date',
        'Weight' => 'weight',
        'Package size' => 'packageSize',
        'Sub-total' => 'subTotal',
        'Shipping cost' => 'shippingCost',
        'Total' => 'total',
        'Currency code' => 'currencyCode',
        'Service code' => 'serviceCode',
        'Signature' => 'signature',
        'Customer title' => 'customerTitle',
        'First name' => 'firstName',
        'Last name' => 'lastName',
        'Full name' => 'fullName',
        'Phone' => 'phone',
        'Email' => 'email',
        'Company name' => 'companyName',
        'Address line 1' => 'addressLine1',
        'Address line 2' => 'addressLine2',
        'Address line 3' => 'addressLine3',
        'City' => 'city',
        'County' => 'county',
        'Postcode' => 'postcode',
        'Country' => 'country',
        'Product SKU' => 'productSku',
        'Customs description' => 'customsDescription',
        'Customs code' => 'customsCode',
        'Country of origin' => 'countryOfOrigin',
        'Quantity' => 'quantity',
        'Unit price' => 'unitPrice',
    ];

    protected $rows = [];

    public function getType(): string
    {
        return ExportDocumentInterface::TYPE_CSV;
    }

    public function getFileExtension(): string
    {
        return 'csv';
    }

    public function addRowData(array $row)
    {
        $this->rows[] = $row;
    }

    public function getData(): string
    {
        $csv = array_map([$this, 'getRow'], $this->rows);
        array_unshift($csv, $this->getHeaderRow());
        return base64_encode(implode(PHP_EOL, $csv));
    }

    protected function getHeaderRow(): string
    {
        return str_putcsv(array_keys($this->headers));
    }

    protected function getRow(array $row): string
    {
        $fields = array_fill_keys(array_values($this->headers), '');
        $rowData = array_merge($fields, array_intersect_key($row, $fields));
        return str_putcsv($rowData);
    }
}