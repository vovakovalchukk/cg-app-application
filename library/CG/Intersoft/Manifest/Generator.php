<?php
namespace CG\Intersoft\Manifest;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\ManifestInterface;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\Manifest;
use CG\Intersoft\Request\Shipment\Confirm as ConfirmShipmentRequest;
use CG\Intersoft\Response\Shipment\Confirm as ConfirmShipmentResponse;
use CG\Intersoft\RoyalMail\CourierAdapter as RoyalMailCourierAdapter;

class Generator
{
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(Account $account): Manifest
    {
        $request = $this->buildRequest($account);
        $response = $this->sendRequest($request, $account);
        return $this->buildManifestFromResponse($response);
    }

    protected function buildRequest(Account $account): ConfirmShipmentRequest
    {
        // We're only using Intersoft for RM right now, if that changes then we'll have to get this carrier code dynamically
        $carrierCode = RoyalMailCourierAdapter::CARRIER_CODE;
        return new ConfirmShipmentRequest($carrierCode);
    }

    protected function sendRequest(ConfirmShipmentRequest $request, Account $account): ConfirmShipmentResponse
    {
        try {
            $client = ($this->clientFactory)($account);
            $client->send($request);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildManifestFromResponse(ConfirmShipmentResponse $response, Account $account): Manifest
    {
        return new Manifest(
            $account,
            $response->getManifestImage(),
            $response->getManifestNumber()
        );
    }
}