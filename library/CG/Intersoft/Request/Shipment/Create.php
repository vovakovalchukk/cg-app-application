<?php
namespace CG\Intersoft\RoyalMail\Request\Shipment;

use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\SaturdayDeliveryInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Email\Attachment\Simple;
use CG\Locale\CountryNameByCode;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Intersoft\RoyalMail\Request\PostAbstract;
use CG\Intersoft\RoyalMail\Response\Shipment\Create as Response;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use SimpleXMLElement;

class Create extends PostAbstract
{
    static $requestNameSpace = 'createShipmentRequest';

    const DATE_FORMAT_SHIPMENT = 'Y-m-d';
    const WEIGHT_UNIT = 'K';
    const DIMENSIONS_UNIT = 'cm';
    const PRODUCT_TYPE = 'NDX'; // DOX for documents, NDX for anything else
    const CURRENCY_DEFAULT = 'GBP';
    const MAX_LEN_REFERENCE = 20;
    const MAX_LEN_DEPARTMENT = 17;
    const MAX_LEN_DEFAULT = 35;
    const MAX_LEN_CONTACT = 40;
    const MAX_LEN_DESCRIPTION_OF_GOODS = 70;
    const MAX_LEN_DESCRIPTION = 255;
    const MAX_LEN_DELIVERY_PHONE_NUMBER = 20;
    const MIN_FINANCIAL_VALUE = 0.01;
    const MAX_ADDRESS_FIELDS = 3;
    const MAX_ADDRESS_FIELDS_LEN = self::MAX_ADDRESS_FIELDS * self::MAX_LEN_DEFAULT;
    const MAX_LEN_HS_CODE = 10;
    const LEN_COUNTRY_CODE = 2;
    const PRE_REGISTRATION_TYPE_EORI = 'EORI';

    const ENHANCEMENT_SIGNATURE = 6;
    const ENHANCEMENT_SATURDAY = 24;

    /** @var Shipment */
    protected $shipment;
    /** @var string */
    protected $requestNamespace;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getUri(): string
    {
        return 'shipments/createShipmentRequest';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function asXml(): string
    {
        $xml = $this->buildXml();
        return $xml->asXml();
    }

    protected function buildXml(): SimpleXMLElement
    {
        $namespace = static::$requestNameSpace;
        $xml = new SimpleXMLElement("<{$namespace}></{$namespace}>");
        $xml = $this->addIntegrationHeader($xml);
        $shipment = $xml->addChild('shipment');
        $shipment = $this->addShipper($shipment);
        $shipment = $this->addDestination($shipment);
        $shipment = $this->addShipmentInformation($shipment);
        return $xml;
    }

    protected function convertLength(float $length): float
    {
        return (new Length($length, ProductDetail::UNIT_LENGTH))->toUnit(static::DIMENSIONS_UNIT);
    }

    protected function getSafePlace(): ?string
    {
        if (!$this->shipment instanceof DeliveryInstructionsInterface) {
            return null;
        }
        return $this->shipment->getDeliveryInstructions();
    }

    protected function addShipper(SimpleXMLElement $xml): SimpleXMLElement
    {
        $collectionAddress = $this->shipment->getCollectionAddress();
        $shipper = $xml->addChild('shipper');
        $shipper->addChild('shipperCompanyName', $this->sanitiseString($collectionAddress->getCompanyName()));
        $shipper->addChild('shipperContactName', $this->sanitiseString($collectionAddress->getFirstName() . ' ' . $collectionAddress->getLastName(), 40));
        $shipper->addChild('shipperAddressLine1', $this->sanitiseString($collectionAddress->getLine1()));
        $shipper->addChild(
        'shipperCity',
            $this->sanitiseString($collectionAddress->getLine4())
            ?: $this->sanitiseString($collectionAddress->getLine3())
            ?: $this->sanitiseString($collectionAddress->getLine5())
        );
        $shipper->addChild('shipperCountryCode', $collectionAddress->getISOAlpha2CountryCode());
        $shipper->addChild('shipperPostCode', $collectionAddress->getPostCode());
        $shipper->addChild('shipperPhoneNumber', $collectionAddress->getPhoneNumber());
        $shipper->addChild('shipperReference', $this->sanitiseString($this->shipment->getCustomerReference(), static::MAX_LEN_REFERENCE));
        $shipper->addChild('shipperDeptCode', $this->sanitiseString($this->getDepartmentReference(), static::MAX_LEN_DEPARTMENT));
        return $xml;
    }

    protected function addDestination(SimpleXMLElement $xml): SimpleXMLElement
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        $destination = $xml->addChild('destination');
        if ($deliveryAddress->getCompanyName()) {
            $destination->addChild('destinationCompanyName', $this->sanitiseString($deliveryAddress->getCompanyName()));
        }

        $deliveryAddress = $this->reformatDestinationAddressLines($deliveryAddress);

        $destination->addChild('destinationContactName', $this->sanitiseString($deliveryAddress->getFirstName() . ' ' . $deliveryAddress->getLastName(), 40));
        $destination->addChild('destinationAddressLine1', $this->sanitiseString($deliveryAddress->getLine1()));
        $destination->addChild('destinationAddressLine2', $this->sanitiseString($deliveryAddress->getLine2()));
        $destination->addChild('destinationAddressLine3', $this->sanitiseString($deliveryAddress->getLine3()));
        $destination->addChild(
            'destinationCity',
            $this->sanitiseString($deliveryAddress->getLine4())
                ?: $this->sanitiseString($deliveryAddress->getLine3())
                ?: $this->sanitiseString($deliveryAddress->getLine5())
        );
        $destination->addChild(
            'destinationCounty',
            $this->sanitiseString($deliveryAddress->getLine5())
                ?: $this->sanitiseString($deliveryAddress->getLine4())
        );
        $destination->addChild('destinationCountryCode', $deliveryAddress->getISOAlpha2CountryCode());
        $destination->addChild('destinationPostCode', $deliveryAddress->getPostCode());
        $destination->addChild(
            'destinationContactName',
            $this->sanitiseString(
                $deliveryAddress->getFirstName() . ' ' . $deliveryAddress->getLastName(),
                static::MAX_LEN_CONTACT
            )
        );
        $destination->addChild('destinationPhoneNumber', $this->getDeliveryPhoneNumber());
        $destination->addChild('destinationEmailAddress', $deliveryAddress->getEmailAddress());
        return $xml;
    }

    protected function addShipmentInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        $shipmentInformation = $xml->addChild('shipmentInformation');
        $shipmentInformation->addChild('shipmentDate', $this->shipment->getCollectionDate()->format(static::DATE_FORMAT_SHIPMENT));
        $shipmentInformation->addChild('serviceCode', $this->shipment->getDeliveryService()->getReference());
        $shipmentInformation = $this->addServiceOptions($shipmentInformation);
        $shipmentInformation = $this->addCustomsInformation($shipmentInformation);
        $shipmentInformation = $this->addPackageInformation($shipmentInformation);
        $shipmentInformation = $this->addItemInformation($shipmentInformation);
        $shipmentInformation = $this->addShipmentOverview($shipmentInformation);
        return $xml;
    }

    protected function addServiceOptions(SimpleXMLElement $xml): SimpleXMLElement
    {
        /** @var Package $firstPackage */
        $firstPackage = $this->shipment->getPackages()[0];
        $serviceOptions = $xml->addChild('serviceOptions');
        $serviceOptions->addChild('postingLocation', $this->getPostingLocationNumber());
        $serviceOptions->addChild('serviceLevel', '01');
        $serviceOptions->addChild('serviceFormat', $firstPackage->getType()->getReference());
        $serviceOptions->addChild('safePlace', $this->getSafePlace());
        $serviceOptions = $this->addServiceEnhancements($serviceOptions);
        return $xml;
    }

    protected function getEnhancementsArray(): array
    {
        $enhancements = [];
        if ($this->shipment instanceof InsuranceOptionsInterface && $this->shipment->getInsuranceOption() != null) {
            $enhancements[] = $this->shipment->getInsuranceOption()->getReference();
        }
        if ($this->shipment instanceof SignatureRequiredInterface && $this->shipment->isSignatureRequired()) {
            $enhancements[] = static::ENHANCEMENT_SIGNATURE;
        }
        if ($this->shipment instanceof SaturdayDeliveryInterface && $this->shipment->isSaturdayDeliveryRequired()) {
            $enhancements[] = static::ENHANCEMENT_SATURDAY;
        }
        return $enhancements;
    }

    protected function addServiceEnhancements(SimpleXMLElement $xml): SimpleXMLElement
    {
        $serviceEnhancementsArray = $this->getEnhancementsArray();
        if (!count($serviceEnhancementsArray) > 0) {
            return $xml;
        }

        $serviceEnhancements = $xml->addChild('serviceEnhancements');
        foreach ($serviceEnhancementsArray as $serviceEnhancementCode) {
            $serviceEnhancements->addChild('serviceEnhancementCode', $serviceEnhancementCode);
        }
        return $xml;
    }

    protected function addCustomsInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $customsInformationXml = $xml->addChild('customsInformation');
        $customsInformationXml->addChild('preRegistrationNumber', $this->shipment->getEoriNumber());
        $customsInformationXml->addChild('preRegistrationType', static::PRE_REGISTRATION_TYPE_EORI);
        $customsInformationXml->addChild('shippingCharges', number_format($this->shipment->getShippingCharges(), 2));
        return $xml;
    }

    protected function addPackageInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        $packagesXml = $xml->addChild('packages');;
        $packageId = 1;

        /** @var Package $package */
        foreach ($packages as $package) {
            $packageXml = $packagesXml->addChild('package');
            $packageXml->addChild('packageId', $packageId++);

            $packageXml->addChild('weight', $package->getWeight());

            $length = $this->convertLength($package->getLength());
            $width = $this->convertLength($package->getWidth());
            $height = $this->convertLength($package->getHeight());

            if ($length == 0 || $width == 0 || $height == 0) {
                continue;
            }

            $packageXml->addChild('length', $length);
            $packageXml->addChild('width', $width);
            $packageXml->addChild('height', $height);
        }

        return $xml;
    }

    protected function addItemInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        $totalWeight = count($packages) === 1 ? $this->getTotalPackageWeight() : 0;
        /** @var Package $package */
        foreach ($packages as $package) {
            foreach ($package->getContents() as $packageContents) {
                $packageWeight = $packageContents->getWeight();
                if ($packageWeight == 0 && $totalWeight > 0 && $packageContents->getQuantity() > 0) {
                    $packageWeight = $totalWeight/$packageContents->getQuantity();
                }

                $itemInformation = $xml->addChild('itemInformation');
                $itemInformation->addChild('itemHsCode', $this->sanitiseString($packageContents->getHSCode(), static::MAX_LEN_HS_CODE));
                $itemInformation->addChild('itemDescription', $this->sanitiseString($packageContents->getDescription(), static::MAX_LEN_DESCRIPTION));
                $itemInformation->addChild('itemQuantity', $packageContents->getQuantity());
                $itemInformation->addChild('itemValue', $this->sanitiseFinancialValue($packageContents->getUnitValue()));
                $itemInformation->addChild('itemCOO', $this->sanitiseItemCountryOfOrigin($packageContents->getOrigin()));
                $itemInformation->addChild('itemNetWeight', $packageWeight);
            }
        }
        return $xml;
    }

    protected function addShipmentOverview(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packageOverviewDetails = $this->getOverviewDetailsArray();
        $xml->addChild('totalPackages', count($this->shipment->getPackages()));
        $xml->addChild('totalWeight', $this->getTotalPackageWeight());
        $xml->addChild('weightId', static::WEIGHT_UNIT);
        $xml->addChild('product', static::PRODUCT_TYPE);
        $xml->addChild('descriptionOfGoods', $packageOverviewDetails['description']);
        $xml->addChild('declaredValue', $packageOverviewDetails['totalValue']);
        $xml->addChild('declaredCurrencyCode', $packageOverviewDetails['currencyCode']);
        return $xml;
    }

    protected function sanitiseFinancialValue(float $value): float
    {
        if ($value > 0) {
            return $value;
        }
        return static::MIN_FINANCIAL_VALUE;
    }

    protected function getOverviewDetailsArray(): array
    {
        $packages = $this->shipment->getPackages();
        $details = [
            'description' => '',
            'totalValue' => 0,
            'currencyCode' => static::CURRENCY_DEFAULT
        ];

        /** @var Package $package */
        foreach ($packages as $package) {
            foreach ($package->getContents() as $packageContent) {
                $details['description'] .=  $packageContent->getDescription() . '|';
                $details['currencyCode'] =  $packageContent->getUnitCurrency();
                $details['totalValue'] += $this->sanitiseFinancialValue($packageContent->getUnitValue()) * $packageContent->getQuantity();
            }
            $details['description'] = $this->sanitiseString(rtrim($details['description'], '|'), static::MAX_LEN_DESCRIPTION_OF_GOODS);
        }
        return $details;
    }

    protected function sanitiseString(?string $string = null, ?int $maxLength = null): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars(mb_substr($string, 0, $maxLength ?? static::MAX_LEN_DEFAULT));
    }

    protected function sanitiseItemCountryOfOrigin(string $itemCountryOfOrigin): string
    {
        return strtoupper($this->sanitiseString(
            $this->verifyItemCountryOfOrigin($itemCountryOfOrigin),
            static::LEN_COUNTRY_CODE
        ));
    }

    protected function verifyItemCountryOfOrigin(string $itemCountryOfOrigin): string
    {
        if (CountryNameByCode::isValidCountryCode($itemCountryOfOrigin)) {
            return $itemCountryOfOrigin;
        }
        return $this->shipment->getCollectionAddress()->getISOAlpha2CountryCode();
    }

    protected function reformatDestinationAddressLines(AddressInterface $deliveryAddress): AddressInterface
    {
        if (!$this->isAnyAddressLineLongerThanMaxValue($deliveryAddress)) {
            return $deliveryAddress;
        }

        $oneLineAddress = $this->combineAllAddressLinesToOne($deliveryAddress);
        if ($this->isOneLineAddressLongerThanMaxAllAddressLineLength($oneLineAddress)) {
            return $deliveryAddress;
        }

        $this->splitAddressIntoLines($deliveryAddress, $oneLineAddress);
        return $deliveryAddress;
    }

    protected function isAnyAddressLineLongerThanMaxValue(AddressInterface $deliveryAddress): bool
    {
        $line1len = mb_strlen($deliveryAddress->getLine1());
        $line2len = mb_strlen($deliveryAddress->getLine2());
        $line3len = mb_strlen($deliveryAddress->getLine3());

        return !($line1len <= static::MAX_LEN_DEFAULT
            && $line2len <= static::MAX_LEN_DEFAULT
            && $line3len <= static::MAX_LEN_DEFAULT
        );
    }

    protected function isOneLineAddressLongerThanMaxAllAddressLineLength(string $oneLineAddress): bool
    {
        return strlen($oneLineAddress) > static::MAX_ADDRESS_FIELDS_LEN;
    }

    protected function combineAllAddressLinesToOne(AddressInterface $deliveryAddress): string
    {
        $address = [
            $deliveryAddress->getLine1(),
            $deliveryAddress->getLine2(),
            $deliveryAddress->getLine3()
        ];

        if (empty($address[2])) {
            unset($address[2]);
        }

        return str_replace(',,', ',', implode(', ', $address));
    }

    protected function splitAddressIntoLines(AddressInterface $deliveryAddress, string $oneLineAddress): void
    {
        $address = explode(PHP_EOL, wordwrap($oneLineAddress, static::MAX_LEN_DEFAULT), static::MAX_ADDRESS_FIELDS);
        $address = array_map('trim', $address);

        $deliveryAddress
            ->setLine1($address[0] ?? null)
            ->setLine2($address[1] ?? null)
            ->setLine3($address[2] ?? null);
    }

    protected function getDeliveryPhoneNumber(): string
    {
        // Intersoft REQUIRE a phone number but it is not enforced by us / most of our channels
        $phoneNumber = $this->shipment->getDeliveryAddress()->getPhoneNumber();
        $phoneNumberLength = strlen($phoneNumber);
        if (!$phoneNumberLength > 0 || !preg_match('|[0-9]+|', $phoneNumber)) {
            return '00000000000';
        }
        if ($phoneNumberLength >= static::MAX_LEN_DELIVERY_PHONE_NUMBER) {
            return $this->shortenPhoneNumber($phoneNumber);
        }
        return $this->shipment->getDeliveryAddress()->getPhoneNumber();
    }

    protected function shortenPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = str_replace(['-', ' '], '', $phoneNumber);
        $phoneNumber = preg_replace('|[^0-9\+]+|', '-', $phoneNumber);
        return substr($phoneNumber, 0, static::MAX_LEN_DELIVERY_PHONE_NUMBER);
    }

    protected function getTotalPackageWeight(): float
    {
        $totalWeight = 0;
        /** @var Package $package */
        foreach ($this->shipment->getPackages() as $package) {
            $totalWeight += $package->getWeight();
        }
        return $totalWeight;
    }

    protected function getDepartmentReference(): string
    {
        $itemCount = 0;
        $packages = $this->shipment->getPackages();
        $skus = [];
        /** @var Package $package */
        foreach ($packages as $package) {
            foreach ($package->getContents() as $packageContents) {
                $itemCount += $packageContents->getQuantity();
                $skus[] = $packageContents->getSku();
            }
        }
        if (count($skus) > 1) {
            return $itemCount . ' ITEMS';
        } else {
            return $itemCount . ' X ' . array_shift($skus);
        }
    }
}