<?php
namespace CourierAdapter\Account\Email;

use CG\Email\Mailer;
use CG\Account\Shared\Entity as AccountEntity;
use Zend\Mime\Mime;

class Service
{
    const EMAIL_SEND_FROM = 'help@channelgrabber.com';
    const EMAIL_SEND_TO = 'mark.duffield@channelgrabber.com';
    const EMAIL_SUBJECT = 'Courier Account Request';
    const EMAIL_BODY_TEMPLATE = "Hello,\n\nOU %s has requested a courier account with the following details:\n\nChannel: %s,\nCompany Name: %s,\nAddress Line 1: %s,\nAddressLine2: %s,\nAddressLine3: %s,\nTown: %s,\nCounty: %s,\nPostcode: %s,\nContact Name: %s,\nPhone Number: %s,\nEmail Address: %s,\nRoyal Mail Account Number: %s,\nPosting Location: %s,\nOBA Email Address: %s";

    /** @var Mailer */
    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendAccountConnectionRequestEmail(AccountEntity $account, array $params)
    {
        $body = sprintf(static::EMAIL_BODY_TEMPLATE,
            $account->getOrganisationUnitId(),
            $params['channel'],
            $params['companyName'],
            $params['addressLine1'],
            $params['addressLine2'] ?? 'N/A',
            $params['addressLine3'],
            $params['town'],
            $params['county'],
            $params['postcode'],
            $params['contactName'],
            $params['phoneNumber'],
            $params['emailAddress'],
            $params['royalMailAccountNumber'],
            $params['royalMailPostingLocation'],
            $params['royalMailObaEmailAddress']
        );
        $this->mailer->sendRaw(
            static::EMAIL_SEND_TO,
            static::EMAIL_SUBJECT,
            $body,
            [],
            static::EMAIL_SEND_FROM,
            Mime::TYPE_TEXT,
            [],
            [],
            null
        );
    }
}