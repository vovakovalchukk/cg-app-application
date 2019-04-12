<?php
namespace Settings\Account;

use CG\Account\Request\Collection as AccountRequestCollection;
use CG\Account\Request\Entity as AccountRequest;
use CG\Account\Request\Filter as AccountRequestFilter;
use CG\Account\Request\StorageInterface as AccountRequestService;
use CG\Partner\Entity as Partner;
use CG\Partner\StorageInterface as PartnerStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Uri\Http as Uri;

class AuthoriseService
{
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

    public function validateToken(?string $token, ?string $userSignature, Uri $uri): void
    {
        if ($token === null) {
            throw new InvalidTokenException('No token has been provided');
        }

        try {
            $filter = (new AccountRequestFilter(1, 1))
                ->setToken($token);

            /** @var AccountRequestCollection $accountRequests */
            $accountRequests = $this->accountRequestService->fetchCollectionByFilter($filter);
            /** @var AccountRequest $accountRequest */
            $accountRequest = $accountRequests->getFirst();

            /** @var Partner $partner */
            $partner = $this->partnerStorage->fetch($accountRequest->getPartnerId());

            if ($userSignature === null) {
                // invalid signature
            }

            $queryString = http_build_query([
                'token' => $token,
                'secret' => $partner->getClientSecret()
            ]);

            $urlWithSecret = $uri->getScheme() . '//' . $uri->getHost() . $uri->getPath() . '?' .$queryString;
            $hashedSignature = hash('sha256', $urlWithSecret);

            if (!hash_equals($hashedSignature, $userSignature)) {
                // Invalid signature, redirect back to partner
            }
        } catch (NotFound $e) {
            throw new InvalidTokenException('The provided token is not valid or it has expired');
        }

    }
}
