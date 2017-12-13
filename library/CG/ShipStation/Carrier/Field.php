<?php
namespace CG\ShipStation\Carrier;

class Field
{
    const DEFAULT_REQUIRED = true;
    const DEFAULT_INPUT_TYPE = 'text';

    protected $name;
    protected $label;
    protected $required;
    protected $inputType;
    protected $value;

    public function __construct(
        string $name,
        string $label,
        bool $required = null,
        string $inputType = null,
        $value = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->inputType = $inputType;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Field
    {
        $this->name = $name;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Field
    {
        $this->label = $label;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): Field
    {
        if ($required === null) {
            $required = static::DEFAULT_REQUIRED;
        }
        $this->required = $required;
        return $this;
    }

    public function getInputType(): string
    {
        return $this->inputType;
    }

    public function setInputType(?string $inputType): Field
    {
        if ($inputType === null) {
            $inputType = static::DEFAULT_INPUT_TYPE;
        }
        $this->inputType = $inputType;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    // Required by Collection
    public function getId()
    {
        return $this->name;
    }
}