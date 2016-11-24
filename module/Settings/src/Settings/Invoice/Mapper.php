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
        $tradingCompanySettings = $settings->getTradingCompanies();

        $data = [
            'organisationUnit' => $tradingCompany->getAddressCompanyName(),
            'organisationUnitId' => $tradingCompany->getId(),
            'assignedInvoice' => [],
            'class' => 'invoiceTradingCompaniesCustomSelect',
            'name' => 'invoiceTradingCompaniesCustomSelect_' . $tradingCompany->getId(),
            'id' => 'invoiceTradingCompaniesCustomSelect_' . $tradingCompany->getId(),
            'sendFromAddress' => [
                'class' => 'invoiceSendFromAddressInput',
                'name' => 'invoiceSendFromAddressInput_' . $tradingCompany->getId(),
                'id' => 'invoiceSendFromAddressInput_' . $tradingCompany->getId(),
            ]
        ];

        if (isset($tradingCompanySettings[$tradingCompany->getId()]['emailSendAs'])) {
            $data['sendFromAddress']['value'] = $settings->getTradingCompanies()[$tradingCompany->getId()]['emailSendAs'];
        }

        if (isset($tradingCompanySettings[$tradingCompany->getId()]['emailVerified'])) {
            $data['sendFromAddress']['emailVerified'] = $settings->getTradingCompanies()[$tradingCompany->getId()]['emailVerified'];
        }

        $selected = false;
        if (isset($tradingCompanySettings[$tradingCompany->getId()]['assignedInvoice'])) {
            $selected = $settings->getTradingCompanies()[$tradingCompany->getId()]['assignedInvoice'];
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