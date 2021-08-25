<?php
namespace CG\UkMail\Consignment\International;

use CG\CourierAdapter\Exception\UserError;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\UkMail\Response\Rest\InternationalConsignment as InternationalConsignmentResponse;
use CG\UkMail\Shipment;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailInternationalConsignmentService';
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

    public function requestInternationalConsignment(
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber,
        string $customsDeclarationType
    ): InternationalConsignmentResponse {
        $this->logDebug(static::LOG_REQUESTING_LABEL_MSG, [$shipment->getAccount()->getId(), $shipment->getCustomerReference()], static::LOG_CODE);
        try {
            $intlConsignmentRequest = $this->mapper->createInternationalConsignmentRequest($shipment, $authToken, $collectionJobNumber, $customsDeclarationType);
            $client = ($this->clientFactory)($shipment->getAccount(), $intlConsignmentRequest);
            return $client->sendRequest($intlConsignmentRequest);
        } catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }
}