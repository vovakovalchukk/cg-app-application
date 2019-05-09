<?php
namespace Partner\Account;

use CG\Account\Request\Collection as AccountRequestCollection;
use CG\Account\Request\Entity as AccountRequest;
use CG\Account\Request\Filter as AccountRequestFilter;
use CG\Account\Request\StorageInterface as AccountRequestService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Partner\Entity as Partner;
use CG\Partner\StatusCodes as PartnerStatusCodes;
use CG\Partner\StorageInterface as PartnerStorage;
use CG\Sso\Client\Service as SsoClient;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG_Login\Service\LoginService;
use CG_Permission\Service as PermissionService;
use Settings\Channel\Service as ChannelService;
use Zend\Session\SessionManager as SessionManager;
use Zend\Uri\Http as Uri;

class AuthoriseService implements LoggerAwareInterface
{
    use LogTrait;

    const MAX_SAVE_RETRIES = 3;
    const SESSION_KEY_ACCOUNT_REQUEST_ID  = 'accountRequestId';

    const LOG_CODE = 'AccountAuthoriseService';
    const LOG_MESSAGE_NO_TOKEN = 'No token has been provided';
    const LOG_MESSAGE_REQUEST_FOUND = 'The request with ID %s was found - partner ID %s - channel %s - region %s';
    const LOG_MESSAGE_INVALID_TOKEN = 'The provided token is not valid or it has expired: %s';
    const LOG_MESSAGE_NO_SIGNATURE = 'No signature has been provided ';
    const LOG_MESSAGE_INVALID_SIGNATURE = 'The provided signature is not valid. User signature: %s . Computed signature %s';
    const LOG_MESSAGE_VALID_URL = 'The provided URL is valid: %s';
    const LOG_MESSAGE_ACCOUNT_REQUEST_EXTRA_FIELD_SETTER_ERROR = 'There was an error while trying to set the field %s with value %s on the AccountRequest entity with ID %s';
    const LOG_ACCOUNT_REQUEST_CONFLICT = 'Conflict occurred while marking the accountRequest with ID %s, retry number %s, as status failed';

    /** @var AccountRequestService */
    protected $accountRequestService;
    /** @var PartnerStorage */
    protected $partnerStorage;
    /** @var UserService */
    protected $userService;
    /** @var SessionManager */
    protected $sessionManager;
    /** @var LoginService */
    protected $loginService;
    /** @var SsoClient */
    protected $ssoClient;
    /** @var ChannelService */
    protected $channelService;
    /** @var NotificationService */
    protected $notificationService;

    public function __construct(
        AccountRequestService $accountRequestService,
        PartnerStorage $partnerStorage,
        UserService $userService,
        SessionManager $sessionManager,
        LoginService $loginService,
        SsoClient $ssoClient,
        ChannelService $channelService,
        NotificationService $notificationService
    ) {
        $this->accountRequestService = $accountRequestService;
        $this->partnerStorage = $partnerStorage;
        $this->userService = $userService;
        $this->sessionManager = $sessionManager;
        $this->loginService = $loginService;
        $this->ssoClient = $ssoClient;
        $this->channelService = $channelService;
        $this->notificationService = $notificationService;
    }

    public function connectAccount(
        AccountRequest $accountRequest,
        Partner $partner,
        ?string $token,
        ?string $userSignature,
        Uri $uri
    ): string {
        $this->validateAccountRequest($accountRequest, $partner);
        $this->validateSignature($token, $accountRequest, $partner, $uri, $userSignature);
        $this->loginUser($accountRequest, $partner);
        return $this->createSalesAccount($accountRequest);
    }

    public function fetchAccountRequestForToken(?string $token): AccountRequest
    {
        if ($token === null) {
            $this->logDebug(static::LOG_MESSAGE_NO_TOKEN, [], static::LOG_CODE);
            throw new InvalidTokenException(static::LOG_MESSAGE_NO_TOKEN);
        }

        try {
            $filter = (new AccountRequestFilter(1, 1))
                ->setToken($token);

            /** @var AccountRequestCollection $accountRequests */
            $accountRequests = $this->accountRequestService->fetchCollectionByFilter($filter);
            /** @var AccountRequest $accountRequest */
            $accountRequest = $accountRequests->getFirst();

            $this->logDebug(static::LOG_MESSAGE_REQUEST_FOUND, [$accountRequest->getId(), $accountRequest->getPartnerId(), $accountRequest->getChannel(), $accountRequest->getRegion()], static::LOG_CODE);

            return $accountRequest;
        } catch (NotFound $e) {
            $this->logWarningException($e, static::LOG_MESSAGE_INVALID_TOKEN, [$token], static::LOG_CODE);
            throw new InvalidTokenException(static::LOG_MESSAGE_INVALID_TOKEN);
        }
    }

    public function fetchPartner(int $partnerId, string $token): Partner
    {
        try {
            /** @var Partner $partner */
            $partner = $this->partnerStorage->fetch($partnerId);
        } catch (NotFound $e) {
            $this->logWarningException($e, static::LOG_MESSAGE_INVALID_TOKEN, [$token], static::LOG_CODE);
            throw new InvalidTokenException(static::LOG_MESSAGE_INVALID_TOKEN);
        }

        return $partner;
    }

    public function fetchPartnerSuccessRedirectUrlFromSession(Account $account): string
    {
        $accountRequest = $this->fetchAccountRequestFromSession();
        $this->markAccountRequestAsSuccessful($accountRequest, $account);

        /** @var Partner $partner */
        $partner = $this->partnerStorage->fetch($accountRequest->getId());

        $this->notificationService->notifyPartner($partner, $accountRequest, $account);

        return $partner->getAccountSuccessRedirectUrl();
    }

    protected function fetchAccountRequestFromSession(): AccountRequest
    {
        $session = $this->sessionManager->getStorage();
        if (!isset($session[PermissionService::PARTNER_MANAGED_LOGIN])
            || !is_array($session[PermissionService::PARTNER_MANAGED_LOGIN])
            || !isset($session[PermissionService::PARTNER_MANAGED_LOGIN][PermissionService::PARTNER_MANAGED_ACCOUNT_AUTHORISE])) {
            throw new NotFound('The account request ID could not be found in the session data');
        }

        $accountRequestId = $session[PermissionService::PARTNER_MANAGED_LOGIN][PermissionService::PARTNER_MANAGED_ACCOUNT_AUTHORISE];
        return $this->accountRequestService->fetch($accountRequestId);
    }

    protected function validateAccountRequest(AccountRequest $request, Partner $partner): void
    {
        if ($request->getStatus() === AccountRequest::STATUS_PENDING) {
            return;
        }

        $this->handleInvalidAccountRequest($request, $partner, PartnerStatusCodes::ACCOUNT_AUTHORISATION_EXPIRED_TOKEN);
    }

    protected function validateSignature(
        string $token,
        AccountRequest $accountRequest,
        Partner $partner,
        Uri $uri,
        ?string $userSignature
    ): void {
        // We need to save it at this point as the Uri object can get mutated
        $accessedUrl = $uri->toString();

        try {
            $this->validateUserSignature($token, $partner, $userSignature, $uri);
        } catch (\InvalidArgumentException $e) {
            $this->handleInvalidAccountRequest($accountRequest, $partner, PartnerStatusCodes::ACCOUNT_AUTHORISATION_INVALID_SIGNATURE);
        }

        $this->logDebug(static::LOG_MESSAGE_VALID_URL, [$accessedUrl], static::LOG_CODE);
    }

    protected function handleInvalidAccountRequest(
        AccountRequest $accountRequest,
        Partner $partner,
        int $statusCode
    ): void {
        $this->markAccountRequestAsFailed($accountRequest);
        throw new InvalidRequestException(
            $this->buildRedirectUrlForPartner($partner, $statusCode)
        );
    }

    protected function fetchUserForOuId(int $ouId): User
    {
        // It's safe to assume that an OU that's managed by partners will have a single user for now.
        // If this changes, will have to update the logic to fetch a specific user.
        $users = $this->userService->fetchCollection(1, 1, $ouId);
        return $users->getFirst();
    }

    protected function validateUserSignature(string $token, Partner $partner, ?string $userSignature, Uri $uri): void
    {
        if ($userSignature === null) {
            $this->logWarning(static::LOG_MESSAGE_NO_SIGNATURE, [], static::LOG_CODE);
            throw new \InvalidArgumentException(static::LOG_MESSAGE_NO_SIGNATURE);
        }

        $uri->setQuery([
            'token' => $token,
            'secret' => $partner->getClientSecret()
        ]);

        $hashedSignature = hash('sha256', $uri->toString());

        if (!hash_equals($hashedSignature, $userSignature)) {
            $this->logWarning(static::LOG_MESSAGE_INVALID_SIGNATURE, [$userSignature, $hashedSignature], static::LOG_CODE);
            throw new \InvalidArgumentException('The provided user signature is not valid');
        }
    }

    protected function buildRedirectUrlForPartner(Partner $partner, int $statusCode): string
    {
        $uri = new Uri($partner->getAccountFailureRedirectUrl());
        $this->appendStatusCodeToUrl($uri, $statusCode);
        return $uri->toString();
    }

    protected function appendStatusCodeToUrl(Uri $uri, int $statusCode): void
    {
        parse_str($uri->getQuery(), $queryArray);
        $queryArray['statusCode'] = $statusCode;
        $uri->setQuery($queryArray);
    }

    protected function markAccountRequestAsFailed(AccountRequest $accountRequest): void
    {
        $this->updateAccountRequestStatus($accountRequest, AccountRequest::STATUS_FAILED);
    }

    protected function markAccountRequestAsSuccessful(AccountRequest $accountRequest, Account $account): void
    {
        $this->updateAccountRequestStatus($accountRequest, AccountRequest::STATUS_CREATED, [
            'accountId' => $account->getId()
        ]);
    }

    protected function updateAccountRequestStatus(
        AccountRequest $accountRequest,
        string $status,
        array $extraFields = []
    ): void {
        for ($retry = 0; $retry < static::MAX_SAVE_RETRIES; $retry++) {
            try {
                $accountRequest->setStatus($status);
                $this->setAdditionalFieldsOnAccountRequest($accountRequest, $extraFields);
                $this->accountRequestService->save($accountRequest);
                return;
            } catch (NotModified $e) {
                return;
            } catch (Conflict $e) {
                /** @var AccountRequest $accountRequest */
                $accountRequest = $this->accountRequestService->fetch($accountRequest->getId());
                $this->logWarningException($e, static::LOG_ACCOUNT_REQUEST_CONFLICT, [$accountRequest->getId(), $retry], static::LOG_CODE);
            }
        }
    }

    protected function setAdditionalFieldsOnAccountRequest(AccountRequest $accountRequest, array $fields): void
    {
        foreach ($fields as $field => $value) {
            $setter = 'set' . ucfirst(strtolower($field));
            if (!method_exists($accountRequest, $setter)) {
                continue;
            }

            try {
                $accountRequest->{$setter}($value);
            } catch (\Throwable $exception) {
                $this->logDebug(static::LOG_MESSAGE_ACCOUNT_REQUEST_EXTRA_FIELD_SETTER_ERROR, [$field, $value, $accountRequest->getId()]);
            }
        }
    }

    protected function loginUser(AccountRequest $accountRequest, Partner $partner): void
    {
        try {
            $user = $this->fetchUserForOuId($accountRequest->getOrganisationUnitId());
            $this->loginService->loginUser($user);
            // Make sure the user in only logged in orders app and not in SSO
            $this->ssoClient->logoutOnSsoService();

            $session = $this->sessionManager->getStorage();
            $session[PermissionService::PARTNER_MANAGED_LOGIN] = [
                PermissionService::PARTNER_MANAGED_ACCOUNT_AUTHORISE => PermissionService::PARTNER_MANAGED_ACCOUNT_AUTHORISE,
                static::SESSION_KEY_ACCOUNT_REQUEST_ID => $accountRequest->getId()
            ];
        } catch (\Throwable $e) {
            $this->logWarningException($e);
            $this->handleInvalidAccountRequest($accountRequest, $partner, PartnerStatusCodes::ACCOUNT_AUTHORISATION_LOGIN_FAILED);
        }
    }

    protected function createSalesAccount(AccountRequest $accountRequest): string
    {
        return $this->channelService->createAccount(
            ChannelType::SALES,
            $accountRequest->getChannel(),
            $accountRequest->getRegion()
        );
    }
}
