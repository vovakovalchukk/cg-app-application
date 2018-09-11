<?php
namespace CG\ShipStation\Carrier;

use CG\ShipStation\Carrier\Field\Collection as FieldCollection;

class Entity
{
    const DEFAULT_ALLOWS_CANCELLATION = true;
    const DEFAULT_ALLOWS_MANIFESTING = true;
    const DEFAULT_ALLOWS_RATES = false;

    protected $channelName;
    protected $displayName;
    protected $salesChannelName;
    protected $allowsCancellation;
    protected $allowsManifesting;
    protected $allowsRates;
    protected $fields;
    /** @var array */
    protected $bookingOptions;
    protected $featureFlag;
    protected $activationDelayed;

    protected $requiredFields = null;

    public function __construct(
        string $channelName,
        FieldCollection $fields,
        ?string $displayName = null,
        ?string $salesChannelName = null,
        ?bool $allowsCancellation = null,
        ?bool $allowsManifesting = null,
        ?bool $allowsRates = null,
        ?array $bookingOptions = null,
        ?string $featureFlag = null,
        bool $activationDelayed = false
    ) {
        $this
            ->setChannelName($channelName)
            ->setFields($fields)
            ->setDisplayName($displayName)
            ->setSalesChannelName($salesChannelName)
            ->setAllowsCancellation($allowsCancellation)
            ->setAllowsManifesting($allowsManifesting)
            ->setAllowsRates($allowsRates)
            ->setBookingOptions($bookingOptions)
            ->setFeatureFlag($featureFlag)
            ->setActivationDelayed($activationDelayed);
    }

    public function getRequiredFieldNames(): array
    {
        if ($this->requiredFields !== null) {
            return $this->requiredFields;
        }
        $this->requiredFields = [];
        foreach ($this->fields as $fieldConfig) {
            // Default to required as most fields will be
            if (!isset($fieldConfig['required']) || $fieldConfig['required'] == true) {
                $this->requiredFields[] = $fieldConfig['name'];
            }
        }
        return $this->requiredFields;
    }

    public function isFieldRequired(string $fieldName): bool
    {
        return in_array($fieldName, $this->getRequiredFieldNames());
    }

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function setChannelName(string $channelName): Entity
    {
        $this->channelName = $channelName;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): Entity
    {
        if (!$displayName) {
            $displayName = ucwords(str_replace(['-', '_'], ' ', $this->channelName));
        }
        $this->displayName = $displayName;
        return $this;
    }

    public function getSalesChannelName(): ?string
    {
        return $this->salesChannelName;
    }

    public function setSalesChannelName(?string $salesChannelName): Entity
    {
        if (!$salesChannelName) {
            $salesChannelName = $this->displayName;
        }
        $this->salesChannelName = $salesChannelName;
        return $this;
    }

    public function isCancellationAllowed(): bool
    {
        return $this->allowsCancellation;
    }

    public function setAllowsCancellation(?bool $allowsCancellation): Entity
    {
        if ($allowsCancellation === null) {
            $allowsCancellation = static::DEFAULT_ALLOWS_CANCELLATION;
        }
        $this->allowsCancellation = $allowsCancellation;
        return $this;
    }

    public function isManifestingAllowed(): bool
    {
        return $this->allowsManifesting;
    }

    public function setAllowsManifesting(?bool $allowsManifesting): Entity
    {
        if ($allowsManifesting === null) {
            $allowsManifesting = static::DEFAULT_ALLOWS_MANIFESTING;
        }
        $this->allowsManifesting = $allowsManifesting;
        return $this;
    }

    public function isAllowsRates(): bool
    {
        return $this->allowsRates;
    }

    public function setAllowsRates(?bool $allowsRates): Entity
    {
        if ($allowsRates === null) {
            $allowsRates = static::DEFAULT_ALLOWS_RATES;
        }
        $this->allowsRates = $allowsRates;
        return $this;
    }

    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    public function setFields(FieldCollection $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function getBookingOptions(): array
    {
        return $this->bookingOptions;
    }

    public function setBookingOptions(array $bookingOptions = null): Entity
    {
        $this->bookingOptions = $bookingOptions ?? [];
        return $this;
    }

    public function getFeatureFlag(): ?string
    {
        return $this->featureFlag;
    }

    public function setFeatureFlag(?string $featureFlag): Entity
    {
        $this->featureFlag = $featureFlag;
        return $this;
    }

    public function isActivationDelayed(): bool
    {
        return $this->activationDelayed;
    }

    public function setActivationDelayed(bool $activationDelayed): Entity
    {
        $this->activationDelayed = $activationDelayed;
        return $this;
    }

    // Required by Collection
    public function getId()
    {
        return $this->channelName;
    }
}
