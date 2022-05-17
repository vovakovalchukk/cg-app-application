<?php
namespace CG\UkMail\Consignment\International;

use CG\CourierAdapter\Address as CAAddress;
use CG\CourierAdapter\Provider\Implementation\Package\Content;
use CG\Locale\CountryNameByAlpha3Code;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\UkMail\Consignment\Domestic\Mapper as DomesticConsignmentMapper;
use CG\UkMail\Consignment\MapperTrait;
use CG\UkMail\CustomsDeclaration\CustomsDeclarationInterface;
use CG\UkMail\CustomsDeclaration\Service as CustomsDeclarationService;
use CG\UkMail\Request\Rest\InternationalConsignment as InternationalConsignmentRequest;
use CG\UkMail\Shipment;
use CG\UkMail\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Mapper
{
    use MapperTrait;

    protected const ROAD_SERVICES = ['204', '206'];

    /** @var CustomsDeclarationService */
    protected $customsDeclarationService;

    public function __construct(CustomsDeclarationService $customsDeclarationService)
    {
        $this->customsDeclarationService = $customsDeclarationService;
    }

    public function createInternationalConsignmentRequest(
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber,
        string $customsDeclarationType
    ): InternationalConsignmentRequest {
        $account = $shipment->getAccount();
        $packages = $shipment->getPackages();
        $deliveryAddress = $shipment->getDeliveryAddress();

        return new InternationalConsignmentRequest(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $this->getAccountNumber($shipment),
            $collectionJobNumber,
            $shipment->getCollectionDate(),
            $this->getDeliveryDetails($deliveryAddress),
            $shipment->getDeliveryService()->getReference(),
            count($packages),
            $this->getCustomerReference($shipment),
            $this->getAlternativeReference($shipment),
            $this->getParcels($packages),
            false,
            $shipment->getIossNumber() ?? null,
            $this->getCustomsDeclaration($shipment, $customsDeclarationType),
            $this->getRecipient($deliveryAddress, $shipment->getEoriNumber()),
            false,
            null,
            false,
            DomesticConsignmentMapper::LABEL_FORMAT_PDF
        );
    }

    protected function getAccountNumber(Shipment $shipment): string
    {
        $account = $shipment->getAccount();
        if (in_array($shipment->getDeliveryService()->getReference(), static::ROAD_SERVICES)) {
            return $account->getCredentials()['intlAccountNumberRoad'];
        }

        return $account->getCredentials()['intlAccountNumberAir'];
    }

    protected function getDeliveryDetails(CAAddress $address): DeliveryInformation
    {
        $deliveryAddresses[] = $this->getDeliveryAddress($address);

        return new DeliveryInformation(
            $this->getContactName($address),
            $address->getPhoneNumber(),
            DomesticConsignmentMapper::CONTACT_NUMBER_TYPE_MOBILE,
            $address->getEmailAddress(),
            $deliveryAddresses
        );
    }

    protected function getAlternativeReference(Shipment $shipment): string
    {
        $totalValue = 0;
        $currencyCode = '';
        $description = [];
        /** @var Package $package */
        foreach ($shipment->getPackages() as $package) {
            /** @var Content $content */
            foreach ($package->getContents() as $content) {
                $totalValue += $content->getUnitValue();
                $currencyCode = $content->getUnitCurrency();
                $description[] = $content->getDescription();
            }
        }

        return substr($totalValue . $currencyCode . ' - ' . implode('|', $description), 0, 20);
    }

    protected function getContactName(CAAddress $address): string
    {
        return $address->getFirstName().' '.$address->getLastName();
    }

    protected function getDeliveryAddress(CAAddress $address, bool $isRecipientAddress = false): Address
    {
        return new Address(
            $address->getCompanyName(),
            $address->getLine1(),
            $address->getLine2(),
            $address->getLine3(),
            $address->determineCityFromAddressLines(),
            $address->determineRegionFromAddressLines(),
            $address->getPostCode(),
            $this->getCountryCode($address),
            $isRecipientAddress ? DomesticConsignmentMapper::ADDRESS_TYPE_RESIDENTIAL : DomesticConsignmentMapper::ADDRESS_TYPE_DOORSTEP
        );
    }

    protected function getCountryCode(CAAddress $address): string
    {
        try {
            return CountryNameByAlpha3Code::getCountryAlpha3CodeFromCountryAlpha2Code($address->getISOAlpha2CountryCode());
        } catch (\Throwable $exception) {
            throw new StorageException($exception->getMessage(), 400, $exception);
        }
    }

    protected function convertWeight(float $weight): float
    {
        return (new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(DomesticConsignmentMapper::WEIGHT_UNIT);
    }

    protected function convertDimension(float $dimension): float
    {
        return (new Length($dimension, ProductDetail::UNIT_LENGTH))->toUnit(DomesticConsignmentMapper::DIMENSION_UNIT);
    }

    /**
     * @param Package[] $packages
     * @return array
     */
    protected function getParcels(array $packages): array
    {
        $parcels = [];
        foreach ($packages as $package) {
            $parcels[] = new Parcel(
                $this->convertDimension($package->getLength()),
                $this->convertDimension($package->getWidth()),
                $this->convertDimension($package->getHeight()),
                number_format($this->convertWeight($package->getWeight()),2)
            );
        }

        return $parcels;
    }

    protected function getRecipient(CAAddress $address, string $eoriNumber): Recipient
    {
        return new Recipient(
            $this->getContactName($address),
            $address->getEmailAddress(),
            $address->getPhoneNumber(),
            $eoriNumber,
            $this->getDeliveryAddress($address, true)
        );
    }

    protected function getCustomsDeclaration(Shipment $shipment, string $customsDeclarationType): CustomsDeclarationInterface
    {
        return $this->customsDeclarationService->getCustomsDeclaration($shipment, $customsDeclarationType);
    }
}