<?php
namespace CG\Hermes\Command;

use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\Hermes\Client;
use CG\Hermes\Request\Generic as GenericRequest;

class Api
{
    /** @var AccountService */
    protected $accountService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var Client */
    protected $client;

    public function __construct(
        AccountService $accountService,
        CAAccountMapper $caAccountMapper,
        Client $client
    ) {
        $this->accountService = $accountService;
        $this->caAccountMapper = $caAccountMapper;
        $this->client = $client;
    }

    public function __invoke(int $accountId, string $method, string $uri, ?string $body = null): string
    {
        $account = $this->accountService->fetch($accountId);
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $request = new GenericRequest($method, $uri, $body);
        $response = $this->client->sendRequest($request, $caAccount);
        return (string)$response;
    }
}