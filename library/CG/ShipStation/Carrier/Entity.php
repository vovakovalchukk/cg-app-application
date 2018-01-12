<?php
namespace CG\ShipStation\Carrier;

use CG\ShipStation\Carrier\Field\Collection as FieldCollection;

class Entity
{
    const DEFAULT_ALLOWS_CANCELLATION = true;
    const DEFAULT_ALLOWS_MANIFESTING = true;

    protected $channelName;
    protected $displayName;
    protected $salesChannelName;
    protected $allowsCancellation;
    protected $allowsManifesting;
    protected $fields;
    /** @var array */
    protected $bookingOptions;

    protected $requiredFields = null;

    public function __construct(
        $channelName,
        FieldCollection $fields,
        $displayName = null,
        $salesChannelName = null,
        $allowsCancellation = null,
        $allowsManifesting = null,
        array $bookingOptions = null
    ) {
        $this
            ->setChannelName($channelName)
            ->setFields($fields)
            ->setDisplayName($displayName)
            ->setSalesChannelName($salesChannelName)
            ->setAllowsCancellation($allowsCancellation)
            ->setAllowsManifesting($allowsManifesting)
            ->setBookingOptions($bookingOptions);
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

    // Required by Collection
    public function getId()
    {
        return $this->channelName;
    }
}