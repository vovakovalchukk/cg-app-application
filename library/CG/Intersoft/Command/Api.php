<?php
namespace CG\Intersoft\Command;

use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\Intersoft\Client;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\Request\Generic as GenericRequest;

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
        /** @var Client $client */
        $client = ($this->clientFactory)($caAccount);
        $response = $client->send($request);
        return (string)$response;
    }
}