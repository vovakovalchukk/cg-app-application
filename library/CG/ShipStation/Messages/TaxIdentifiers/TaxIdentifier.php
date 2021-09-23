<?php
namespace CG\ShipStation\Messages\TaxIdentifiers;

use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class TaxIdentifier
{
    protected const TAXABLE_ENTITY_TYPE_SHIPPER = 'shipper';
    protected const TAXABLE_ENTITY_TYPE_RECIPIENT = 'recipient';
    protected const IDENTIFIER_TYPE_VAT = 'vat';
    protected const IDENTIFIER_TYPE_EORI = 'eori';
    protected const IDENTIFIER_TYPE_SSN = 'ssn';
    protected const IDENTIFIER_TYPE_EIN = 'ein';
    protected const IDENTIFIER_TYPE_TIN = 'tin';
    protected const IDENTIFIER_TYPE_IOSS = 'ioss';
    protected const IDENTIFIER_TYPE_PAN = 'pan';
    protected const IDENTIFIER_TYPE_VOEC = 'voec';

    /** @var string */
    protected $taxableEntityType;
    /** @var string */
    protected $identifierType;
    /** @var string */
    protected $value;
    /** @var string */
    protected $issuingAuthority;

    public function __construct(
        string $taxableEntityType,
        string $identifierType,
        string $value,
        string $issuingAuthority
    ) {
        $this->taxableEntityType = $taxableEntityType;
        $this->identifierType = $identifierType;
        $this->value = $value;
        $this->issuingAuthority = $issuingAuthority;
    }

    public static function createIossNumber(
        Order $order,
        OrganisationUnit $rootOu
    ): TaxIdentifier {
        return new static(
            static::TAXABLE_ENTITY_TYPE_SHIPPER,
            static::IDENTIFIER_TYPE_IOSS,
            $order->getIossNumber(),
            $rootOu->getAddressCountryCode()
        );
    }

    public static function createEoriNumber(
        OrderData $orderData,
        OrganisationUnit $rootOu
    ): TaxIdentifier {
        return new static(
            static::TAXABLE_ENTITY_TYPE_SHIPPER,
            static::IDENTIFIER_TYPE_EORI,
            $orderData->getEoriNumber(),
            $rootOu->getAddressCountryCode()
        );
    }

    public function toArray(): array
    {
        return [
            'taxable_entity_type' => $this->taxableEntityType,
            'identifier_type' => $this->identifierType,
            'value' => $this->value,
            'issuing_authority' => $this->issuingAuthority,
        ];
    }

    public function getTaxableEntityType(): string
    {
        return $this->taxableEntityType;
    }

    public function getIdentifierType(): string
    {
        return $this->identifierType;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getIssuingAuthority(): string
    {
        return $this->issuingAuthority;
    }
}