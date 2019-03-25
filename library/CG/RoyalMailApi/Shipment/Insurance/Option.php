<?php
namespace CG\RoyalMailApi\Shipment\Insurance;

use CG\CourierAdapter\InsuranceOptionInterface;

class Option implements InsuranceOptionInterface
{
    /** @var string */
    protected $displayName;
    /** @var string */
    protected $reference;

    public function __construct(string $reference, string $displayName)
    {
        $this->reference = $reference;
        $this->displayName = $displayName;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    public static function fromArray(array $data): Option
    {
        return new self(
            $data['reference'],
            $data['displayName']
        );
    }
}