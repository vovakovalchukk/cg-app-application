<?php
namespace CG\UkMail\DomesticConsignment;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Request\Rest\DomesticConsignment as DomesticConsignmentRequest;
use CG\UkMail\Response\Rest\DomesticConsignment as DomesticConsignmentResponse;
use CG\UkMail\Shipment;
use CG\UkMail\Client\Factory as ClientFactory;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailDomesticConsignmentService';
    protected const LOG_REQUESTING_LABEL_MSG = 'Requesting UK Mail label for account %d order %s';

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var Mapper */
    protected $mapper;

    public function __construct(ClientFactory $clientFactory, Mapper $mapper)
    {
        $this->clientFactory = $clientFactory;
        $this->mapper = $mapper;
    }

    protected function createDomesticConsignmentRequest(
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber
    ): DomesticConsignmentRequest {
        return $this->mapper->createDomesticConsignmentRequest($shipment, $authToken, $collectionJobNumber);
    }

    public function requestDomesticConsignment(
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber
    ): DomesticConsignmentResponse {
        $this->logDebug(static::LOG_REQUESTING_LABEL_MSG, [$shipment->getAccount()->getId(), $shipment->getCustomerReference()], static::LOG_CODE);
        $domesticConsignmentRequest = $this->createDomesticConsignmentRequest($shipment, $authToken, $collectionJobNumber);
        $client = ($this->clientFactory)($shipment->getAccount(), $domesticConsignmentRequest);
        return $client->sendRequest($domesticConsignmentRequest);
    }
}