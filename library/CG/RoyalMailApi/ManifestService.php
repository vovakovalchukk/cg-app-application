<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Request\Manifest\Create as CreateManifestRequest;
use CG\RoyalMailApi\Response\Manifest\Create as CreateManifestResponse;
use CG\RoyalMailApi\Client\Factory as ClientFactory;

class ManifestService
{
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function createManifest(Account $account)
    {
        /** @var Client $client */
        $client = ($this->clientFactory)($account);
        $request = new CreateManifestRequest();
        /** @var CreateManifestResponse $response */
        return $client->send($request);
    }
}
