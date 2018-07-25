<?php
namespace CG\Hermes\Credentials;

use CG\CourierAdapter\EmailClientInterface;
use CG\Locale\PhoneNumber;
use Psr\Log\LoggerInterface;
use Zend\Form\Form;

class Requester
{
    /** @var string */
    protected $toEmail;
    /** @var string */
    protected $replyEmail;

    public function __construct(string $toEmail, string $replyEmail)
    {
        $this->toEmail = $toEmail;
        $this->replyEmail = $replyEmail;
    }

    public function __invoke(Form $credentialsRequestForm, EmailClientInterface $emailClient, LoggerInterface $logger)
    {
        $company = $credentialsRequestForm->getData()['clientName'];
        if (!$this->toEmail) {
            $logger->info('Not sending credentials request to Hermes for {company} as there\'s no \'to\' address defined.', ['company' => $company]);
            return;
        }
        $subject = 'New Client Integration request - ' . $company;
        $message = $this->generateMessage($credentialsRequestForm);
        $from = ($this->replyEmail !== '' ? $this->replyEmail : null);

        $emailClient->send($this->toEmail, $subject, $message, $from);
        $logger->info('Sent credentials request to Hermes for {company}.', ['company' => $company]);
    }

    protected function generateMessage(Form $credentialsRequestForm): string
    {
        $company = $credentialsRequestForm->getData()['clientName'];
        $companyEmail = $credentialsRequestForm->getData()['email'];
        $message = <<<EOS
Hello,
<br /><br />
Our mutual customer - {$company} - would like to integrate their Hermes account with ChannelGrabber's already built solution which 
uses the Hermes API. To that end could you generate credentials for the SIT environment for the customer?
<br/>
Once done please email them directly to the customer at: {$companyEmail}.
<br />
We kindly request that they be also sent to: {$this->replyEmail} so that we can expediently assist our mutual customer in successfully completing their integration.
<br /><br />
The customer's details are as follows:
<br />
EOS;
        $message .= $this->convertFormToMessage($credentialsRequestForm);
        $message .= <<<EOS
<br />
If you need any more information then please email the customer directly.
<br /><br />
Once they have their test credentials we will then help the customer through generating a test pack for your approval.
<br /><br />
Thank you.
<br />
Kind regards,
<br />
The ChannelGrabber team.
<br />
EOS;
        $message .= PhoneNumber::UK;
        return $message;
    }

    protected function convertFormToMessage(Form $credentialsRequestForm): string
    {
        $message = '<table border="1" cellpadding="3">';
        foreach ($credentialsRequestForm->getData() as $field => $value) {
            $label = $this->convertFieldToLabel($field);
            $message .= '<tr><th>' . $label . '</th><td>' . $value . '</td></tr>';
        }
        $message .= '</table>';
        return $message;
    }

    protected function convertFieldToLabel(string $field): string
    {
        return ucfirst($field);
    }
}