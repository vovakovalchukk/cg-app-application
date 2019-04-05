<?php
namespace CG\Intersoft\RoyalMail\DeliveryService;

class Option
{
    /** @var string */
    protected $name;
    /** @var array|null */
    protected $countries;

    public function __construct(string $name, ?array $countries = null)
    {
        $this->name = $name;
        $this->countries = $countries;
    }

    public static function fromArray(array $array): Option
    {
        return new static($array['name'], $array['countries'] ?? null);
    }

    /**
     * @return Option[]
     */
    public static function multipleFromArrayOfArrays(array $array): array
    {
        $options = [];
        foreach ($array as $name => $optionArray) {
            $optionArray['name'] = $name;
            $options[$name] = static::fromArray($optionArray);
        }
        return $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountries(): ?array
    {
        return $this->countries;
    }
}