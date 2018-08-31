<?php
namespace CG\Hermes\Command;

use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\Hermes\Client\Factory as ClientFactory;
use CG\Hermes\Request\Generic as GenericRequest;

class Api
{
    /** @var AccountService */
    protected $accountService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(
        AccountService $accountService,
        CAAccountMapper $caAccountMapper,
        ClientFactory $clientFactory
    ) {
        $this->accountService = $accountService;
        $this->caAccountMapper = $caAccountMapper;
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(int $accountId, string $method, string $uri, ?string $body = null): string
    {
        $account = $this->accountService->fetch($accountId);
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $request = new GenericRequest($method, $uri, $body);
        $client = ($this->clientFactory)($caAccount);
        $response = $client->sendRequest($request);
        return (string)$response;
    }
}