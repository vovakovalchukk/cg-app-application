<?php
namespace ShipStation\Webhook;

use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Client;
use CG\ShipStation\Request\Webhook\Create as WebhookRequest;
use CG\ShipStation\Webhook\Notification\Service as NotificationService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Helper\RemoteUrl;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var RemoteUrl */
    protected $remoteUrl;
    /** @var Client*/
    protected $client;
    /** @var NotificationService */
    protected $notificationService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        RemoteUrl $remoteUrl,
        Client $client,
        NotificationService $notificationService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->remoteUrl = $remoteUrl;
        $this->client = $client;
        $this->notificationService = $notificationService;
    }

    public function registerForCarrierConnectedWithActiveUser(Account $account, Account $shipStationAccount): void
    {
        $request = $this->buildWebhookRequest($shipStationAccount->getRootOrganisationUnitId());
        $this->client->sendRequest($request, $shipStationAccount);
        $this->saveActiveUserForNotificationOfConnection($account);
    }

    protected function buildWebhookRequest(int $organisationUnitId): WebhookRequest
    {
        return new WebhookRequest(
            WebhookRequest::EVENT_CARRIER_CONNECTED,
            ($this->remoteUrl)('/shipstation/carrierConnected/' . $organisationUnitId, 'hook')
        );
    }

    protected function saveActiveUserForNotificationOfConnection(AccountEntity $account): void
    {
        $user = $this->activeUserContainer->getActiveUser();
        $this->notificationService->saveUserToNotifyOfCarrierConnected($user, $account);
    }
}