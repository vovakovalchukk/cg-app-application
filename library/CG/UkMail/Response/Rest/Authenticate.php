<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;
use CG\UkMail\Authenticate\Account;

class Authenticate extends AbstractRestResponse implements ResponseInterface
{
    /** @var string */
    protected $authenticationToken;
    /** @var Account[] */
    protected $accounts = [];

    public function __construct(string $authenticationToken, array $accounts)
    {
        $this->authenticationToken = $authenticationToken;
        $this->accounts = $accounts;
    }

    public static function createResponse($response): ResponseInterface
    {
        $body = $response[0];
        $authenticationToken = $body['authenticationToken'];

        $accounts = [];
        foreach ($body['accounts'] as $account) {
            $accounts[] = Account::fromArray($account);
        }

        return new static($authenticationToken, $accounts);
    }

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function getAccounts(): array
    {
        return $this->accounts;
    }
}