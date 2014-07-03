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
        $name = $tradingCompany->getAddressCompanyName() != null ? $tradingCompany->getAddressCompanyName() : '(unnamed)';

        $data = [
            "organisationUnit" => $name,
            'organisationUnitId' => $tradingCompany->getId(),
            "assignedInvoice" => []
        ];

        $tradingCompanySettings = $settings->getTradingCompanies();
        if (! isset($tradingCompanySettings[$tradingCompany->getId()])) {
            $default = false;
        } else {
            $default = $settings->getTradingCompanies()[$tradingCompany->getId()];
        }

        foreach ($invoices as $invoice) {
            $data['assignedInvoice'][] = [
                'id' => $invoice->getId(),
                'name' => $invoice->getName(),
                'selected' => $invoice->getId() == $default,
            ];
        }
        return $data;
    }
}