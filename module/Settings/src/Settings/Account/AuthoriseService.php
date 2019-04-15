<?php
namespace Settings\Account;

use CG\Account\Request\Collection as AccountRequestCollection;
use CG\Account\Request\Entity as AccountRequest;
use CG\Account\Request\Filter as AccountRequestFilter;
use CG\Account\Request\StorageInterface as AccountRequestService;
use CG\Partner\Entity as Partner;
use CG\Partner\StatusCodes as PartnerStatusCodes;
use CG\Partner\StorageInterface as PartnerStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Uri\Http as Uri;

class AuthoriseService implements LoggerAwareInterface
{
    use LogTrait;

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
            throw new InvalidTokenException('No token has been provided');
        }

        try {
            $accountRequest = $this->fetchAccountRequest($token);
            /** @var Partner $partner */
            $partner = $this->partnerStorage->fetch($accountRequest->getPartnerId());
        } catch (NotFound $e) {
            $this->logWarningException($e);
            /** @var Partner $partner */
            throw new InvalidTokenException('The provided token is not valid or it has expired');
        }

        try {
            $this->validateUserSignature($token, $partner, $userSignature, $uri);
        } catch (\InvalidArgumentException $e) {
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
            throw new \InvalidArgumentException('User signature not provided');
        }

        $queryString = http_build_query([
            'token' => $token,
            'secret' => $partner->getClientSecret()
        ]);

        $urlWithSecret = $uri->getScheme() . '//' . $uri->getHost() . $uri->getPath() . '?' .$queryString;
        $hashedSignature = hash('sha256', $urlWithSecret);

        if (!hash_equals($hashedSignature, $userSignature)) {
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
}
