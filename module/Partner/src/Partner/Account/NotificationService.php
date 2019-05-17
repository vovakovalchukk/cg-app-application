<?php
namespace Partner\Account;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Partner\Notification\Client as NotificationClient;
use Partner\Notification\Request\Account as AccountNotificationRequest;
use CG\Account\Request\Entity as AccountRequest;
use CG\Partner\Entity as Partner;
use CG\Account\Shared\Entity as Account;

class NotificationService implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_MESSAGE_NOTIFICATION_SUCCESSFUL = 'Partner %s was notified successfully with account ID %s and account request ID %s';
    const LOG_MESSAGE_EXCEPTION = 'We\'ve encountered an exception while notifying our partner that the account has been successfully created';
    const LOG_CODE = 'PartnerAccountNotificationService';

    /** @var NotificationClient */
    protected $notificationClient;

    public function __construct(NotificationClient $notificationClient)
    {
        $this->notificationClient = $notificationClient;
    }

    public function notifyPartner(Partner $partner, AccountRequest $accountRequest, Account $account): void
    {
        $this->addGlobalLogEventParams(['partnerId' => $partner->getId(), 'accountId' => $account->getId(), 'accountRequestId' => $accountRequest->getId(), 'ou' => $account->getOrganisationUnitId()]);

        try {
            $this->notificationClient->sendRequest(
                $partner,
                $this->buildAccountNotificationRequest($partner, $accountRequest, $account)
            );
            $this->logDebug(static::LOG_MESSAGE_NOTIFICATION_SUCCESSFUL, [$partner->getId(), $account->getId(), $accountRequest->getId()], [static::LOG_CODE]);
        } catch (\Throwable $exception) {
            // We have to make sure that we catch all exceptions here so that the code doesn't stop just because the partner notification endpoint didn't work
            $this->logWarningException($exception, static::LOG_MESSAGE_EXCEPTION, [], [static::LOG_CODE]);
        }

        $this->removeGlobalLogEventParams(['partnerId', 'accountId', 'accountRequestId']);
    }

    protected function buildAccountNotificationRequest(Partner $partner, AccountRequest $accountRequest, Account $account): AccountNotificationRequest
    {
        return new AccountNotificationRequest(
            $partner->getAccountNotificationUrl(),
            $account->getId(),
            $accountRequest->getId()
        );
    }
}
