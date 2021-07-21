<?php
namespace CG\UkMail\DeliveryProducts;

use CG\CourierAdapter\Exception\UserError;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Shipment;
use CG\UkMail\Response\Rest\DeliveryProducts as DeliveryProductsResponse;
use CG\UkMail\Request\Rest\DeliveryProducts as DeliveryProductsRequest;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailDeliveryProductsService';
    protected const LOG_REQUESTING_LABEL_MSG = 'Requesting UK Mail label for account %d order %s';

    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function getDeliveryProducts(
        Shipment $shipment,
        string $authToken
    ):DeliveryProductsResponse  {
        $this->logDebug(static::LOG_REQUESTING_LABEL_MSG, [$shipment->getAccount()->getId(), $shipment->getCustomerReference()], static::LOG_CODE);
        $deliveryProductsRequest = $this->createDeliveryProductsRequest($shipment, $authToken);
        try {
            $client = ($this->clientFactory)($shipment->getAccount(), $deliveryProductsRequest);
            return $client->sendRequest($deliveryProductsRequest);
        } catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }

    protected function createDeliveryProductsRequest(
        Shipment $shipment,
        string $authToken
    ):DeliveryProductsRequest {

    }

}