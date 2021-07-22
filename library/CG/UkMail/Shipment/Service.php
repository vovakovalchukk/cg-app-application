<?php
namespace CG\UkMail\Shipment;

use CG\UkMail\Authenticate\Service as AuthenticateService;
use CG\UkMail\Collection\Service as CollectionService;
use CG\UkMail\Consignment\Domestic\Service as DomesticConsignmentService;
use CG\UkMail\Consignment\International\Service as InternationalConsignmentService;
use CG\UkMail\DeliveryProducts\Service as DeliveryProductsService;
use CG\UkMail\Response\ConsignmentInterface;
use CG\UkMail\Shipment;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
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
    /** @var DeliveryProductsService  */
    protected $deliveryProductsService;
    /** @var InternationalConsignmentService  */
    protected $internationalConsignmentService;

    public function __construct(
        AuthenticateService $authenticateService,
        CollectionService $collectionService,
        DomesticConsignmentService $domesticConsignmentService,
        DeliveryProductsService $deliveryProductsService,
        InternationalConsignmentService $internationalConsignmentService
    ) {
        $this->authenticateService = $authenticateService;
        $this->collectionService = $collectionService;
        $this->domesticConsignmentService = $domesticConsignmentService;
        $this->deliveryProductsService = $deliveryProductsService;
        $this->internationalConsignmentService = $internationalConsignmentService;
    }

    public function bookShipment(Shipment $shipment): Shipment
    {
        $account = $shipment->getAccount();
        $collectionDate = $shipment->getCollectionDate();

        $authToken = $this->authenticateService->getAuthenticationToken($account);
        $collectionJobNumber = $this->collectionService->getCollectionJobNumber($account, $authToken, $collectionDate);
        if ($shipment->getDeliveryService()->isDomesticService()) {
            $response = $this->domesticConsignmentService->requestDomesticConsignment($shipment, $authToken, $collectionJobNumber);
        } else {
            $deliveryProduct = $this->deliveryProductsService->checkIntlServiceAvailabilityForShipment($shipment);
            if (!isset($deliveryProduct)) {
                throw new UserError('Selected shipping service is not supported for country or your account');
            }
            $response = $this->internationalConsignmentService->requestInternationalConsignment($shipment, $authToken, $collectionJobNumber, $deliveryProduct->getCustomsDeclaration());
        }
        return $this->updateShipmentFromResponse($shipment, $response);
    }

    protected function updateShipmentFromResponse(Shipment $shipment, ConsignmentInterface $response): Shipment
    {
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