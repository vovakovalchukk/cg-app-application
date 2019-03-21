<?php
namespace CG\RoyalMailApi\Package;

use CG\CourierAdapter\Package\TypeInterface;

class Type implements TypeInterface
{
    /** @var string */
    protected $reference;
    /** @var string */
    protected $displayName;

    public function __construct(string $reference, string $displayName)
    {
        $this->reference = $reference;
        $this->displayName = $displayName;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public static function fromArray(array $data): TypeInterface
    {
        return new self(
            $data['reference'],
            $data['displayName']
        );
    }
}