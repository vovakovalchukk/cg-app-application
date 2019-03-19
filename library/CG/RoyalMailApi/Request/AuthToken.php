<?php
namespace CG\RoyalMailApi\Request;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\Request\GetAbstract;
use CG\RoyalMailApi\Response\AuthToken as Response;

class AuthToken extends GetAbstract
{
    public function getUri(): string
    {
        return 'token';
    }

    public function getAdditionalHeaders(Account $account, Credentials $credentials): array
    {
        return [
            'x-rmg-user-name' => $credentials->getUsername(),
            'x-rmg-password' => $this->hashAndEncodePassword($credentials->getPassword()),
        ];
    }

    protected function hashAndEncodePassword(string $password): string
    {
        return base64_encode(sha1($password));
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}