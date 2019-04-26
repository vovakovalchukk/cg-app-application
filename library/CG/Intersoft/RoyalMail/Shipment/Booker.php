<?php
namespace CG\Intersoft\RoyalMail\Shipment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Exception\UserError;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\Intersoft\Client;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Intersoft\RoyalMail\Request\Shipment\Create as CreateRequest;
use CG\Intersoft\RoyalMail\Response\Shipment\Create as CreateResponse;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Documents\Generator as DocumentsGenerator;
use function CG\Stdlib\mergePdfData;
use CG\Intersoft\RoyalMail\Package as RoyalMailPackage;
use CG\Stdlib\Exception\Storage as StorageException;

class Booker
{
    const DOMESTIC_COUNTRY = 'GB';
    const COUNTRY_CODE_USA = 'US';
    const ONE_D_BARCODE_PATTERN = '/[A-Z]{2}[0-9]{9}GB/';
    const SHIP_NO_SEP = '|';

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var DocumentsGenerator */
    protected $documentsGenerator;

    public function __construct(
        ClientFactory $clientFactory,
        DocumentsGenerator $documentsGenerator
    ) {
        $this->clientFactory = $clientFactory;
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

    protected function isUsShipment(Shipment $shipment): bool
    {
        return ($shipment->getDeliveryAddress()->getISOAlpha2CountryCode() == static::COUNTRY_CODE_USA);
    }

    protected function sendRequest(CreateRequest $request, CourierAdapterAccount $account): CreateResponse
    {
        try {
            /** @var Client $client */
            $client = ($this->clientFactory)($account);
            return $client->send($request);
        } catch (StorageException $e) {
            $this->handleUserErrors($e);
        } catch (\Exception $e) {
            throw new OperationFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function updateShipmentFromResponse(Shipment $shipment, CreateResponse $response): Shipment
    {
        $rmPackages = $response->getPackages();
        $shipmentNumbers = [];
        /** @var RoyalMailPackage $rmPackage */
        foreach ($rmPackages as $rmPackage) {
            $shipmentNumbers[] = $rmPackage->getTrackingNumber();
        }
        $shipment->setCourierReference(implode(static::SHIP_NO_SEP, $shipmentNumbers));

        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            $rmPackage = current($rmPackages);
            if (!$rmPackage) {
                break;
            }
            $label = $response->getLabelImage();
            // We do not want to request a CN23 for US shipments as it is merged with the label by Intersoft already
            if (!$this->isDomesticShipment($shipment) && !$this->isUsShipment($shipment)) {
                $documentData = $this->fetchInternationalDocumentsForShipmentItem($rmPackage->getTrackingNumber(), $shipment);
                $label = $this->mergeInternationalDocumentsIntoLabel($label, $documentData);
            }
            if ($label) {
                $package->setLabel(new Label($label, LabelInterface::TYPE_PDF));
            }
            $package->setTrackingReference($rmPackage->getTrackingNumber());
            next($rmPackages);
        }
        return $shipment;
    }

    protected function fetchInternationalDocumentsForShipmentItem(string $trackingNumber, Shipment $shipment): ?string
    {
        return ($this->documentsGenerator)($trackingNumber, $shipment);
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

    protected function handleUserErrors(\Exception $exception): void
    {
        if ($exception->getCode() === 500) {
            throw $exception;
        }
        throw new UserError($exception->getMessage(), $exception->getCode(), $exception);
    }
}