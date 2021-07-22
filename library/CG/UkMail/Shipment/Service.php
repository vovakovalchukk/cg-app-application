<?php
namespace CG\UkMail\Shipment;

use CG\UkMail\Authenticate\Service as AuthenticateService;
use CG\UkMail\Collection\Service as CollectionService;
use CG\UkMail\Consignment\Domestic\Service as DomesticConsignmentService;
use CG\UkMail\Shipment;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Response\Rest\DomesticConsignment as DomesticConsignmentResponse;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\CourierAdapter\LabelInterface;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    /** @var AuthenticateService */
    protected $authenticateService;
    /** @var CollectionService */
    protected $collectionService;
    /** @var DomesticConsignmentService */
    protected $domesticConsignmentService;


    public function __construct(
        AuthenticateService $authenticateService,
        CollectionService $collectionService,
        DomesticConsignmentService $domesticConsignmentService
    ) {
        $this->authenticateService = $authenticateService;
        $this->collectionService = $collectionService;
        $this->domesticConsignmentService = $domesticConsignmentService;
    }

    public function bookShipment(Shipment $shipment): Shipment
    {
        $account = $shipment->getAccount();
        $collectionDate = $shipment->getCollectionDate();

        $authToken = $this->authenticateService->getAuthenticationToken($account);
        $collectionJobNumber = $this->collectionService->getCollectionJobNumber($account, $authToken, $collectionDate);
        $domesticConsignmentResponse = $this->domesticConsignmentService->requestDomesticConsignment($shipment, $authToken, $collectionJobNumber);
        return $this->updateShipmentFromResponse($shipment, $domesticConsignmentResponse);
    }

    protected function updateShipmentFromResponse(Shipment $shipment, DomesticConsignmentResponse $response): Shipment
    {
//        if (!empty($response->getErrorMessages())) {
//            throw new UserError(implode('; ', $response->getErrorMessages()));
//        }

        $identifiers = $response->getIdentifiers();

        $courierReference = $identifiers[0] ? $identifiers[0]->getIdentifierValue() : '';
        $shipment->setCourierReference($courierReference);
        $labels = $response->getLabels();
        foreach ($shipment->getPackages() as $package) {
            $labelData = current($labels);
            if ($labelData) {
                $package->setLabel(new Label($labelData->getLabel(), LabelInterface::TYPE_PDF));
            }
            $identifier = current($identifiers) ?? null;
            $trackingReference = isset($identifier) ? $identifier->getIdentifierValue() : '';
            $package->setTrackingReference($trackingReference);
            next($labels);
            next($identifier);
        }
        return $shipment;
    }
}