<?php
namespace CG\UkMail\Consignment;

class Identifier
{
    /** @var string */
    protected $identifierType;
    /** @var string */
    protected $identifierValue;

    public function __construct(string $identifierType, string $identifierValue)
    {
        $this->identifierType = $identifierType;
        $this->identifierValue = $identifierValue;
    }

    public function getIdentifierType(): string
    {
        return $this->identifierType;
    }

    public function getIdentifierValue(): string
    {
        return $this->identifierValue;
    }
}