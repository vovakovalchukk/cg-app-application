<?php
namespace Settings\Invoice;

use CG\OrganisationUnit\Entity as OrganisationUnitEntity;
use CG\Template\Collection as TemplateCollection;
use CG\Settings\Invoice\Shared\Entity as InvoiceSettingsEntity;
use CG\Settings\InvoiceMapping\Entity as InvoiceMappingEntity;
use CG\Amazon\Aws\Ses\Service as AmazonSesService;

class Mapper
{
    public function toDataTableArray(
        OrganisationUnitEntity $tradingCompany,
        array $invoices,
        InvoiceSettingsEntity $settings
    ) {
        $tradingCompanySettings = $this->getTradingCompanySettings($settings, $tradingCompany);

        $data = [
            'organisationUnit' => $tradingCompany->getAddressCompanyName(),
            'organisationUnitId' => $tradingCompany->getId(),
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
                'emailVerificationStatusClass' => isset($tradingCompanySettings['emailVerificationStatus']) ? strtolower($tradingCompanySettings['emailVerificationStatus']) : '',
            ]
        ];

        $emailVerifiedStatus = isset($tradingCompanySettings['emailVerificationStatus']) ? $tradingCompanySettings['emailVerificationStatus'] : null;
        $data['sendFromAddress']['emailVerificationStatus'] = $this->getEmailVerificationStatusMessage($emailVerifiedStatus);
        return $data;
    }

    protected function getEmailVerificationStatusMessage($emailVerifiedStatus)
    {
        $message = '';
        $message = ($emailVerifiedStatus === AmazonSesService::STATUS_FAILED) ? AmazonSesService::STATUS_MESSAGE_FAILED : $message;
        $message = ($emailVerifiedStatus === AmazonSesService::STATUS_PENDING) ? AmazonSesService::STATUS_MESSAGE_PENDING : $message;
        $message = ($emailVerifiedStatus === AmazonSesService::STATUS_VERIFIED) ? AmazonSesService::STATUS_MESSAGE_VERIFIED : $message;

        return $message;
    }

    protected function getTradingCompanySettings($settings, $tradingCompany)
    {
        $tradingCompanySettings = $settings->getTradingCompanies();
        $tradingCompanySettings = array_filter($tradingCompanySettings, function($record) use ($tradingCompany) {
            return ($record['id'] == $tradingCompany->getId());
        });

        if (! empty($tradingCompanySettings)) {
            return array_pop($tradingCompanySettings);
        }

        return [];
    }
}
