<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Manifest as Manifest;
use CG\RoyalMailApi\Request\Manifest\Create as CreateManifestRequest;
use CG\RoyalMailApi\Request\Manifest\PrintManifest as PrintManifestRequest;
use CG\RoyalMailApi\Response\Manifest\Create as CreateManifestResponse;
use CG\RoyalMailApi\Response\Manifest\PrintManifest as PrintManifestResponse;
use CG\CourierAdapter\Exception\OperationFailed as OperationFailedException;

class ManifestService
{
    // The wait time between requests and the maximum number of retires should be adjusted after we try generating manifests with a real RM account
    const WAIT_TIME = 20;
    const MAX_RETRIES = 15;

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
            return $this->buildManifestResponseFromCreateResponse($createManifestResponse, $account);
        }

        return $this->sendPrintManifestRequest($account, $client, $createManifestResponse);
    }

    protected function sendCreateManifestRequest(Client $client): CreateManifestResponse
    {
        try {
            $request = new CreateManifestRequest();
            /** @var CreateManifestResponse $response */
            $response = $client->send($request);
            return $response;
        } catch (\Exception $e) {
            throw new OperationFailedException('There was an error while creating a manifest on the RM API', $e->getCode(), $e);
        }
    }

    protected function sendPrintManifestRequest(
        Account $account,
        Client $client,
        CreateManifestResponse $createManifestResponse
    ): Manifest {

        for ($retry = 0; $retry < static::MAX_RETRIES; $retry++) {
            $request = new PrintManifestRequest($createManifestResponse->getBatchNumber());
            /** @var PrintManifestResponse $printManifestResponse */
            $printManifestResponse = $client->send($request);

            if ($printManifestResponse->getManifest()) {
                return $this->buildManifestFromPrintResponse($printManifestResponse, $request, $account);
            }

            sleep(static::WAIT_TIME);
        }

        throw new \Exception('Couldn\'t generate a manifest on Royal Mail');
    }

    protected function buildManifestResponseFromCreateResponse(
        CreateManifestResponse $response,
        Account $account
    ): Manifest {
        return new Manifest($account, $response->getManifest(), $response->getBatchNumber());
    }

    protected function buildManifestFromPrintResponse(
        PrintManifestResponse $response,
        PrintManifestRequest $request,
        Account $account
    ): Manifest {
        return new Manifest($account, $response->getManifest(), $request->getManifestBatchNumber());
    }
}
