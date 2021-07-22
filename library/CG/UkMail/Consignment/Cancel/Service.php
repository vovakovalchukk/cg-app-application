<?php
namespace CG\UkMail\Consignment\Cancel;

use CG\CourierAdapter\Exception\UserError;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\UkMail\Shipment;
use CG\UkMail\Request\Soap\CancelConsignment as CancelConsignmentRequest;
use CG\UkMail\Response\Soap\CancelConsignment as CancelConsignmentResponse;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailCancelConsignmentService';
    protected const LOG_REQUESTING_CANCELLATION_MSG = 'Requesting UK Mail label cancellation for account %d order %s';

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var Mapper */
    protected $mapper;

    public function __construct(ClientFactory $clientFactory, Mapper $mapper)
    {
        $this->clientFactory = $clientFactory;
        $this->mapper = $mapper;
    }

    protected function createCancelConsignmentRequest(Shipment $shipment, string $authToken): CancelConsignmentRequest
    {
        return $this->mapper->createCancelConsignmentRequest($shipment, $authToken);
    }

    public function requestCancelConsignment(Shipment $shipment, string $authToken): CancelConsignmentResponse
    {
        $this->logDebug(static::LOG_REQUESTING_CANCELLATION_MSG, [$shipment->getAccount()->getId(), $shipment->getCustomerReference()], static::LOG_CODE);
        $cancelConsignmentRequest = $this->createCancelConsignmentRequest($shipment, $authToken);
        try {
            $client = ($this->clientFactory)($shipment->getAccount(), $cancelConsignmentRequest);
            return $client->sendRequest($cancelConsignmentRequest);
        } catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }

}