<?php
namespace CG\Intersoft\RoyalMail\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\Intersoft\Client;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\RoyalMail\Request\Shipment\Create as CreateRequest;
use CG\Intersoft\RoyalMail\Response\Shipment\Completed\Item as ShipmentItem;
use CG\Intersoft\RoyalMail\Response\Shipment\Create as CreateResponse;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Documents\Generator as DocumentsGenerator;
use CG\Intersoft\RoyalMail\Shipment\Label\Generator as LabelGenerator;
use function CG\Stdlib\mergePdfData;
use CG\Intersoft\RoyalMail\Package as RoyalMailPackage;

class Booker
{
    const DOMESTIC_COUNTRY = 'GB';
    const ONE_D_BARCODE_PATTERN = '/[A-Z]{2}[0-9]{9}GB/';
    const SHIP_NO_SEP = '|';

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var LabelGenerator */
    protected $labelGenerator;
    /** @var DocumentsGenerator */
    protected $documentsGenerator;

    public function __construct(
        ClientFactory $clientFactory,
        LabelGenerator $labelGenerator,
        DocumentsGenerator $documentsGenerator
    ) {
        $this->clientFactory = $clientFactory;
        $this->labelGenerator = $labelGenerator;
        $this->documentsGenerator = $documentsGenerator;
    }

    public function __invoke(Shipment $shipment): Shipment
    {
        $request = $this->buildRequestFromShipment($shipment);
        $response = $this->sendRequest($request, $shipment->getAccount());
        return $this->updateShipmentFromResponse($shipment, $response);
    }

    protected function buildRequestFromShipment(Shipment $shipment): CreateRequest
    {
        return new CreateRequest($shipment);
    }

    protected function isDomesticShipment(Shipment $shipment): bool
    {
        return ($shipment->getDeliveryAddress()->getISOAlpha2CountryCode() == static::DOMESTIC_COUNTRY);
    }

    protected function sendRequest(CreateRequest $request, CourierAdapterAccount $account): CreateResponse
    {
        try {
            /** @var Client $client */
            $client = ($this->clientFactory)($account);
            return $client->send($request);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function updateShipmentFromResponse(Shipment $shipment, CreateResponse $response): Shipment
    {
        $rmPackages = $response->getPackages();
        $shipmentNumbers = [];
        /** @var RoyalMailPackage $package */
        foreach ($rmPackages as $rmPackage) {
            $shipmentNumbers[] = $rmPackage->getUniqueId();
        }
        $shipment->setCourierReference(implode(static::SHIP_NO_SEP, $shipmentNumbers));

        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            $rmPackage = current($rmPackages);
            if (!$rmPackage) {
                break;
            }
            $label = $response->getLabelImage();
            if ($label) {
                $package->setLabel(new Label($label, LabelInterface::TYPE_PDF));
            }
            $package->setTrackingReference($rmPackage->getTrackingNumber());
            next($rmPackages);
        }
        return $shipment;
    }

    protected function fetchLabelForShipmentItem(ShipmentItem $shipmentItem, Shipment $shipment): ?string
    {
        $labelData = ($this->labelGenerator)($shipmentItem, $shipment);
        if (!$this->isDomesticShipment($shipment)) {
            $documentData = $this->fetchInternationalDocumentsForShipmentItem($shipmentItem, $shipment);
            $labelData = $this->mergeInternationalDocumentsIntoLabel($labelData, $documentData);
        }
        return $labelData;
    }

    protected function fetchInternationalDocumentsForShipmentItem(ShipmentItem $shipmentItem, Shipment $shipment): ?string
    {
        return ($this->documentsGenerator)($shipmentItem, $shipment);
    }

    protected function mergeInternationalDocumentsIntoLabel(string $labelData, ?string $documentsData): string
    {
        if (!$documentsData) {
            return $labelData;
        }
        $mergedPdfData = mergePdfData([base64_decode($labelData), base64_decode($documentsData)]);
        return base64_encode($mergedPdfData);
    }

    protected function determineTrackingNumber(ShipmentItem $shipmentItem): ?string
    {
        // The shipmentNumber is the 1D barcode when present, otherwise it's an RM internal ID
        if (preg_match(static::ONE_D_BARCODE_PATTERN, $shipmentItem->getShipmentNumber())) {
            return $shipmentItem->getShipmentNumber();
        }
        // Fallback to the 2D barcode number
        return $shipmentItem->getItemId();
    }
}