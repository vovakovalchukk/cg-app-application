<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Manifest\Create as CreateManifestRequest;
use CG\RoyalMailApi\Request\Manifest\CreateImage as CreateManifestImageRequest;
use CG\RoyalMailApi\Response\Manifest\Create as CreateManifestResponse;
use CG\RoyalMailApi\Response\Manifest\CreateImage as CreateManifestImageResponse;
use CG\RoyalMailApi\Response\Manifest\Response as ManifestResponse;

class ManifestService
{
    // The wait time between requests and the maximum number of retires should be adjusted after we try generating manifests with a real RM account
    const WAIT_TIME = 60;
    const MAX_RETRIES = 10;

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

        $createManifestResponse = $this->sendCreateManifestRequest($client);
        if ($createManifestResponse->getManifest() !== null) {
            return $createManifestResponse->buildManifestResponseForAccount($account);
        }

        return $this->sendCreateManifestImageRequest($account, $client, $createManifestResponse);
    }

    protected function sendCreateManifestRequest(Client $client): CreateManifestResponse
    {
        $request = new CreateManifestRequest();
        /** @var CreateManifestResponse $response */
        $response = $client->send($request);
        return $response;
    }

    protected function sendCreateManifestImageRequest(
        Account $account,
        Client $client,
        CreateManifestResponse $createManifestResponse
    ): ManifestResponse {

        $retry = 0;
        do {
            $request = new CreateManifestImageRequest($createManifestResponse->getBatchNumber());
            /** @var CreateManifestImageResponse $manifestImageResponse */
            $manifestImageResponse = $client->send($request);

            if ($manifestImageResponse->getManifest()) {
                return $this->buildManifestResponse($manifestImageResponse, $request, $account);
            }

            $retry++;
            sleep(static::WAIT_TIME);
        } while ($retry < static::MAX_RETRIES);

        throw new \Exception('Couldn\'t generate a manifest on Royal Mail');
    }

    protected function buildManifestResponse(
        CreateManifestImageResponse $response,
        CreateManifestImageRequest $request,
        Account $account
    ): ManifestResponse {
        return new ManifestResponse($account, $response->getManifest(), $request->getManifestBatchNumber());
    }
}
