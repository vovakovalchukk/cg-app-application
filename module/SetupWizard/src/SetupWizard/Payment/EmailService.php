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

    const EMAIL_TEMPLATE = 'orderhub/email_upgrade_package_error';

    protected $mailer;

    public function __construct(
        Mailer $mailer
    ) {
        $this->mailer = $mailer;
    }

    public function sendErrorToSupport()
    {

    }

    protected function send()
    {
        $this->logCritical(static::LOG_MSG_PREPARE, [], static::LOG_CODE);
        $this->mailer->send(
            $emailAddresses,
            $this->createSubject($changeType),
            $this->createViewModel($changeType)
        );
        $this->logCritical(static::LOG_MSG_SENT, [], static::LOG_CODE);
    }

    protected function createSubject($changeType)
    {
        return sprintf(
            $this->subjectMap[$changeType],
            ($this->currentPackage ? $this->currentPackage->getName() : 'N/A'),
            ($this->newPackage ? $this->newPackage->getName() : 'N/A')
        );
    }

    protected function createViewModel(): ViewModel
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

        return (new ViewModel($variables))->setTemplate(static::EMAIL_TEMPLATE);
    }
}