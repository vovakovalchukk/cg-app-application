<?php
namespace CG\Hermes\Credentials\Request;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Account\CredentialRequest\TestPackFile;
use CG\CourierAdapter\Address;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\Hermes\DeliveryService\Service as DeliveryServiceService;
use CG\Hermes\Shipment;
use CG\Hermes\Shipment\Package;
use CG\Hermes\Shipment\Service as ShipmentService;
use iio\libmergepdf\Merger as PDFMerger;

class TestPackGenerator
{
    const DEFAULT_COUNTRY_CODE = 'GB';

    /** @var DeliveryServiceService */
    protected $deliveryServiceService;
    /** @var ShipmentService */
    protected $shipmentService;
    /** @var array */
    protected $shipmentsData;

    public function __construct(
        DeliveryServiceService $deliveryServiceService,
        ShipmentService $shipmentService,
        array $shipmentsData = []
    ) {
        $this->deliveryServiceService = $deliveryServiceService;
        $this->shipmentService = $shipmentService;
        $this->shipmentsData = $shipmentsData;
    }

    /**
     * @return TestPackFile[]
     */
    public function getTestPackFileList(): array
    {
        return [
            new TestPackFile('TEST_PACK_LABELS.pdf', 'labels')
        ];
    }

    public function generateTestPackFile(TestPackFile $file, Account $account, AddressInterface $collectionAddress): string
    {
        if ($file->getReference() != 'labels') {
            throw new \UnexpectedValueException(__METHOD__ . ' did not expect a test file of "' . $file->getReference() . '"');
        }
        $shipments = $this->mapDataToShipments($account, $collectionAddress);
        $bookedShipments = $this->bookShipments($shipments);
        $mergedLabelPdf = $this->mergeShipmentLabelPdfs($bookedShipments);
        return $this->convertLabelPdfToDataUri($mergedLabelPdf);
    }

    protected function mapDataToShipments(Account $account, AddressInterface $collectionAddress): array
    {
        $defaultShipmentData = ['account' => $account, 'collectionAddress' => $collectionAddress, 'collectionDateTime' => new \DateTime()];
        $shipments = [];
        foreach ($this->shipmentsData as $shipmentData) {
            $shipmentData['deliveryAddress'] = $this->mapDataToAddress($shipmentData['deliveryAddress']);
            $shipmentData['deliveryService'] = $this->deliveryServiceService->getDeliveryServiceByReference($shipmentData['deliveryService']);
            $shipmentData['packages'] = $this->mapPackagesDataToPackages($shipmentData['packages']);
            $shipmentData = array_merge($defaultShipmentData, $shipmentData);
            $shipments[] = Shipment::fromArray($shipmentData);
        }
        return $shipments;
    }

    protected function mapDataToAddress(array $addressData): Address
    {
        return new Address(
            $addressData['companyName'] ?? '',
            $addressData['firstName'],
            $addressData['lastName'],
            $addressData['line1'],
            $addressData['line2'],
            $addressData['line3'],
            $addressData['line4'],
            $addressData['line5'],
            $addressData['postCode'],
            $addressData['country'] ?? '',
            $addressData['ISOAlpha2CountryCode'] ?? static::DEFAULT_COUNTRY_CODE,
            $addressData['emailAddress'] ?? '',
            $addressData['phoneNumber'] ?? ''
        );
    }

    protected function mapPackagesDataToPackages(array $packagesData): array
    {
        $defaultPackageData = ['weight' => 0, 'length' => 0, 'width' => 0, 'height' => 0, 'contents' => []];
        $packages = [];
        for ($count = 0; $count < count($packagesData); $count++) {
            $packageData = $packagesData[$count];
            $defaultPackageData['number'] = $count+1;
            $packageData = array_merge($defaultPackageData, $packageData);
            $packages[] = Package::fromArray($packageData);
        }
        return $packages;
    }

    protected function bookShipments(array $shipments): array
    {
        $bookedShipments = [];
        foreach ($shipments as $shipment) {
            $bookedShipments[] = $this->shipmentService->bookShipment($shipment);
        }
        return $bookedShipments;
    }

    protected function mergeShipmentLabelPdfs(array $shipments): string
    {
        $pdfMerger = new PDFMerger();
        /** @var Shipment $shipment */
        foreach ($shipments as $shipment) {
            /** @var Label $label */
            foreach ($shipment->getLabels() as $label) {
                $pdfMerger->addRaw(base64_decode($label->getData()));
            }
        }
        return $pdfMerger->merge();
    }

    protected function convertLabelPdfToDataUri(string $labelPdf): string
    {
        return 'data:'. Label::TYPE_PDF .';base64,' . base64_encode($labelPdf);
    }
}