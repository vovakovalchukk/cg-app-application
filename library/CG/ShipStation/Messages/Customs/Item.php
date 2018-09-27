<?php
namespace CG\ShipStation\Messages\Customs;

use CG\Order\Shared\Item\Entity as OrderItem;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Item
{
    /** @var string */
    protected $description;
    /** @var int */
    protected $quantity;
    /** @var float */
    protected $value;
    /** @var string */
    protected $countryOfOrigin;
    /** @var string|null */
    protected $harmonizedTariffCode;
    /** @var string|null */
    protected $id;

    public function __construct(
        string $description,
        int $quantity,
        float $value,
        string $countryOfOrigin,
        ?string $harmonizedTariffCode = null,
        ?string $id = null
    ) {
        $this->description = $description;
        $this->quantity = $quantity;
        $this->value = $value;
        $this->countryOfOrigin = $countryOfOrigin;
        $this->harmonizedTariffCode = $harmonizedTariffCode;
        $this->id = $id;
    }

    public static function createFromOrderItem(
        OrderItem $orderItem,
        OrganisationUnit $ou
    ): self {
        return new self(
            $orderItem->getItemName(),
            $orderItem->getItemQuantity(),
            $orderItem->getIndividualItemPrice(),
            $ou->getAddressCountryCode()
        );
    }

    public function toArray(): array
    {
        $array = [
            'description' => $this->getDescription(),
            'quantity' => $this->getQuantity(),
            'value' => $this->getValue(),
            'country_of_origin' => $this->getCountryOfOrigin(),
            // Note: id is never sent, only ever generated by ShipEngine
        ];
        // ShipEngine doesnt handle null values
        if ($this->getHarmonizedTariffCode()) {
            $array['harmonized_tariff_code'] = $this->getHarmonizedTariffCode();
        }
        return $array;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCountryOfOrigin(): string
    {
        return $this->countryOfOrigin;
    }

    public function getHarmonizedTariffCode(): ?string
    {
        return $this->harmonizedTariffCode;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}