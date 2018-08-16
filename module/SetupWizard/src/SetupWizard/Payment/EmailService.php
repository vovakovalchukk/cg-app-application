<?php
namespace SetupWizard\Payment;

use CG\Billing\Subscription\Collection as Subscriptions;
use CG\Billing\Subscription\Entity as Subscription;
use CG\Email\Mailer;
use CG\Locale\UserLocaleInterface as UserLocale;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use Zend\View\Model\ViewModel;

class EmailService implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'SetupWizardPaymentEmailService';
    const LOG_MSG_PREPARE = 'Preparing to send email for subscription error';
    const LOG_MSG_SENT = 'Sent email for subscription error';

    const EMAIL_SUBJECT = 'A user may have experienced an error during subscription payment';
    const EMAIL_TEMPLATE = 'orderhub/email_upgrade_package_error';

    const SUPPORT_EMAIL_ADDRESS = 'help@channelgrabber.com';
    const BILLING_EMAIL_ADDRESS = 'billing@channelgrabber.com';

    protected $mailer;
    protected $emailAddresses;


    public function __construct(
        Mailer $mailer,
        array $emailAddresses
    ) {
        $this->mailer = $mailer;
        $this->emailAddresses = $emailAddresses;
    }

    public function sendErrorToSupport(Subscriptions $subscriptions, OrganisationUnit $organisationUnit)
    {
        $viewModel = $this->createErrorViewModel(static::EMAIL_TEMPLATE, $subscriptions, $organisationUnit);
        $this->send($this->emailAddresses, static::EMAIL_SUBJECT, $viewModel);
    }

    protected function send(array $emailAddresses, string $emailSubject, ViewModel $viewModel)
    {
        $this->logCritical(static::LOG_MSG_PREPARE, [], static::LOG_CODE);
        $this->mailer->send(
            $emailAddresses,
            $emailSubject,
            $viewModel
        );
        $this->logCritical(static::LOG_MSG_SENT, [], static::LOG_CODE);
    }

    protected function createErrorViewModel(
        string $emailTemplate,
        Subscriptions $subscriptions,
        OrganisationUnit $organisationUnit
    ): ViewModel {
        $variables = [
            'locale' => $organisationUnit->getLocale(),
            'companyId' => $organisationUnit->getId(),
            'companyName' => $organisationUnit->getAddressCompanyName(),
            'subscriptions' => $subscriptions,
        ];

        return (new ViewModel($variables))->setTemplate($emailTemplate);
    }
}