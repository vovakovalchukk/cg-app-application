<?php
namespace Settings\Account;

use CG\Account\Request\Collection as AccountRequestCollection;
use CG\Account\Request\Entity as AccountRequest;
use CG\Account\Request\Filter as AccountRequestFilter;
use CG\Account\Request\Service as AccountRequestService;
use CG\Stdlib\Exception\Runtime\NotFound;

class AuthoriseService
{
    /** @var AccountRequestService */
    protected $accountRequestService;

    public function validateToken(?string $token = null): void
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
        } catch (NotFound $e) {
            throw new InvalidTokenException('The provided token is not valid or it has expired');
        }

        var_dump($accountRequest);
        die;
    }
}
