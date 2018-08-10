<?php
namespace ShipStation\Webhook;

use CG\Account\Shared\Entity as Account;
use CG\Http\StatusCode as HttpStatusCode;
use CG\ShipStation\Client;
use CG\ShipStation\Request\Webhook\Create as WebhookRequest;
use CG\ShipStation\Webhook\Notification\Service as NotificationService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Helper\RemoteUrl;
use Guzzle\Http\Exception\ClientErrorResponseException;

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
        $this->sendWebhookRegistrationRequest($request, $shipStationAccount);
        $this->saveActiveUserForNotificationOfConnection($account);
    }

    protected function buildWebhookRequest(int $organisationUnitId): WebhookRequest
    {
        return new WebhookRequest(
            WebhookRequest::EVENT_CARRIER_CONNECTED,
            ($this->remoteUrl)('/shipstation/carrierConnected/' . $organisationUnitId, 'hook')
        );
    }

    protected function sendWebhookRegistrationRequest(WebhookRequest $request, Account $shipStationAccount): void
    {
        try {
            $this->client->sendRequest($request, $shipStationAccount);
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == HttpStatusCode::CONFLICT) {
                // No-op. ShipEngine doesn't allow the same hook to be registered twice,
                // so this just means its already set up for this OU
                return;
            }
            throw $e;
        }
    }

    protected function saveActiveUserForNotificationOfConnection(Account $account): void
    {
        $user = $this->activeUserContainer->getActiveUser();
        $this->notificationService->saveUserToNotifyOfCarrierConnected($user, $account);
    }
}