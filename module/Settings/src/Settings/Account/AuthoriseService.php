<?php
namespace Settings\Account;

use CG\Account\Request\Collection as AccountRequestCollection;
use CG\Account\Request\Entity as AccountRequest;
use CG\Account\Request\Filter as AccountRequestFilter;
use CG\Account\Request\StorageInterface as AccountRequestService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Partner\Entity as Partner;
use CG\Partner\StatusCodes as PartnerStatusCodes;
use CG\Partner\StorageInterface as PartnerStorage;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Uri\Http as Uri;

class AuthoriseService implements LoggerAwareInterface
{
    use LogTrait;

    const MAX_SAVE_RETRIES = 3;

    const LOG_CODE = 'AccountAuthoriseService';
    const LOG_MESSAGE_NO_TOKEN = 'No token has been provided';
    const LOG_MESSAGE_INVALID_TOKEN = 'The provided token is not valid or it has expired: %s';
    const LOG_MESSAGE_NO_SIGNATURE = 'No signature has been provided ';
    const LOG_MESSAGE_INVALID_SIGNATURE = 'The provided signature is not valid. User signature: %s . Computed signature %s';

    /** @var AccountRequestService */
    protected $accountRequestService;

    /** @var PartnerStorage */
    protected $partnerStorage;

    public function __construct(
        AccountRequestService $accountRequestService,
        PartnerStorage $partnerStorage
    ) {
        $this->accountRequestService = $accountRequestService;
        $this->partnerStorage = $partnerStorage;
    }

    public function validateRequest(?string $token, ?string $userSignature, Uri $uri): void
    {
        if ($token === null) {
            $this->logDebug(static::LOG_MESSAGE_NO_TOKEN, [], static::LOG_CODE);
            throw new InvalidTokenException(static::LOG_MESSAGE_NO_TOKEN);
        }

        try {
            $accountRequest = $this->fetchAccountRequest($token);
            /** @var Partner $partner */
            $partner = $this->partnerStorage->fetch($accountRequest->getPartnerId());
        } catch (NotFound $e) {
            $this->logDebugException($e, static::LOG_MESSAGE_INVALID_TOKEN, [$token], static::LOG_CODE);
            /** @var Partner $partner */
            throw new InvalidTokenException(static::LOG_MESSAGE_INVALID_TOKEN);
        }

        try {
            $this->validateUserSignature($token, $partner, $userSignature, $uri);
        } catch (\InvalidArgumentException $e) {
            $this->markAccountRequestAsFailed($accountRequest);
            throw new InvalidRequestException(
                $this->buildRedirectUrlForPartner($partner, PartnerStatusCodes::ACCOUNT_AUTHORISATION_INVALID_SIGNATURE)
            );
        }
    }

    protected function fetchAccountRequest(string $token): AccountRequest
    {
        $filter = (new AccountRequestFilter(1, 1))
            ->setToken($token);

        /** @var AccountRequestCollection $accountRequests */
        $accountRequests = $this->accountRequestService->fetchCollectionByFilter($filter);
        /** @var AccountRequest $accountRequest */
        $accountRequest = $accountRequests->getFirst();

        return $accountRequest;
    }

    protected function validateUserSignature(string $token, Partner $partner, ?string $userSignature, Uri $uri): void
    {
        if ($userSignature === null) {
            $this->logDebug(static::LOG_MESSAGE_NO_SIGNATURE, [], static::LOG_CODE);
            throw new \InvalidArgumentException(static::LOG_MESSAGE_NO_SIGNATURE);
        }

        $uri->setQuery([
            'token' => $token,
            'secret' => $partner->getClientSecret()
        ]);

        $hashedSignature = hash('sha256', $uri->toString());

        if (!hash_equals($hashedSignature, $userSignature)) {
            $this->logDebug(static::LOG_MESSAGE_INVALID_SIGNATURE, [$userSignature, $hashedSignature], static::LOG_CODE);
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
        for ($retry = 0; $retry < static::MAX_SAVE_RETRIES; $retry++) {
            try {
                $accountRequest->setStatus(AccountRequest::STATUS_FAILED);
                $this->accountRequestService->save($accountRequest);
                return;
            } catch (NotModified $e) {
                // Nothing to do, entity already marked as failed
                return;
            } catch (Conflict $e) {
                /** @var AccountRequest $accountRequest */
                $accountRequest = $this->accountRequestService->fetch($accountRequest->getId());
                // log here
            }
        }
    }
}
