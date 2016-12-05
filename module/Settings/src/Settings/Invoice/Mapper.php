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
        $tradingCompanySettings = $this->getTradingCompanySettings($settings, $tradingCompany);

        $data = [
            'organisationUnit' => $tradingCompany->getAddressCompanyName(),
            'organisationUnitId' => $tradingCompany->getId(),
            'assignedInvoice' => [],
            'class' => 'invoiceTradingCompaniesCustomSelect',
            'name' => 'invoiceTradingCompaniesCustomSelect_' . $tradingCompany->getId(),
            'id' => 'invoiceTradingCompaniesCustomSelect_' . $tradingCompany->getId(),
            'sendFromAddress' => [
                'class' => 'invoiceSendFromAddressInput email-verify-input',
                'name' => 'invoiceSendFromAddressInput_' . $tradingCompany->getId(),
                'id' => $tradingCompany->getId(),
                'buttonId' => 'invoiceSendFromAddressVerifyButton_' . $tradingCompany->getId(),
                'buttonClass' => 'email-verify-button',
				'value' => isset($tradingCompanySettings['emailSendAs']) ? $tradingCompanySettings['emailSendAs'] : null,
                'emailVerified' => isset($tradingCompanySettings['emailVerified']) ? $tradingCompanySettings['emailVerified'] : null,
            ]
        ];
        $selected = isset($tradingCompanySettings['assignedInvoice']) ? $tradingCompanySettings['assignedInvoice'] : false;

        foreach ($invoices as $invoice) {
            $data['options'][] = [
                'title' => $invoice->getName(),
                'value' => $invoice->getId(),
                'selected' => $invoice->getId() == $selected
            ];
        }
        return $data;
    }

    protected function getTradingCompanySettings($settings, $tradingCompany)
    {
        $tradingCompanySettings = $settings->getTradingCompanies();
        $tradingCompanySettings = array_filter($tradingCompanySettings, function($record) use ($tradingCompany) {
            return ($record['id'] === $tradingCompany->getId());
        });

        if (isset($tradingCompanySettings[0])) {
            return $tradingCompanySettings[0];
        }

        return [];
    }
}