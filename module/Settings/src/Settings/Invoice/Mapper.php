<?php
namespace Settings\Invoice;

use CG\OrganisationUnit\Entity as OrganisationUnitEntity;
use CG\Template\Collection as TemplateCollection;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;

class Mapper
{
    public function toDataTableArray(
        OrganisationUnitEntity $tradingCompany,
        TemplateCollection $invoices,
        InvoiceSettingsEntity $settings
    ) {
        $data = [
            "organisationUnit" => $tradingCompany->getAddressCompanyName(),
            'organisationUnitId' => $tradingCompany->getId(),
            "assignedInvoice" => []
        ];

        $data['class'] = 'invoiceTradingCompaniesCustomSelect';
        $data['name'] = 'invoiceTradingCompaniesCustomSelect_' . $tradingCompany->getId();
        $data['id'] = $data['name'];

        $tradingCompanySettings = $settings->getTradingCompanies();

        $selected = false;
        if (isset($tradingCompanySettings[$tradingCompany->getId()])) {
            $selected = $settings->getTradingCompanies()[$tradingCompany->getId()];
        }

        foreach ($invoices as $invoice) {
            $data['options'][] = [
                'title' => $invoice->getName(),
                'value' => $invoice->getId(),
                'selected' => $invoice->getId() == $selected
            ];
        }
        return $data;
    }
}