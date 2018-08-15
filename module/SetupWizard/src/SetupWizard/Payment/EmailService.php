<?php
namespace SetupWizard\Payment;

use CG\Email\Mailer;
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

    public function sendErrorToSupport()
    {
        $emailAdresses = [
            static::SUPPORT_EMAIL_ADDRESS,
            static::BILLING_EMAIL_ADDRESS
        ];

        $viewModel = $this->createErrorViewModel(static::EMAIL_TEMPLATE);
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

    protected function createErrorViewModel($emailTemplate): ViewModel
    {
        $variables = [
            'locale' => ($this->ou ? $this->ou->getLocale() : UserLocale::LOCALE_DEFAULT),
            'companyId' => ($this->ou ? $this->ou->getId() : ''),
            'companyName' => ($this->ou ? $this->ou->getAddressCompanyName() : ''),
            'currentPackage' => ($this->currentPackage ? $this->currentPackage->getName() : 'N/A'),
            'newPackage' => ($this->newPackage ? $this->newPackage->getName() : 'N/A'),
            'amount' => ($this->transaction ? $this->transaction->getAmount() : null),
            'paymentMethod' => ($this->transaction ? $this->transaction->getType() : 'N/A'),
            'discountName' => ($this->discount ? $this->discount->getName() : 'NONE APPLIED'),
        ];

        return (new ViewModel($variables))->setTemplate($emailTemplate);
    }
}