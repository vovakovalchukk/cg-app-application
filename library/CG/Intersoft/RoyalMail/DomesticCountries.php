<?php
namespace CG\Intersoft\RoyalMail;

class DomesticCountries
{
    protected const UNITED_KINGDOM = 'UK';
    protected const GREAT_BRITAIN = 'GB';
    protected const ISLE_OF_MAN = 'IM';
    protected const JERSEY = 'JE';
    protected const GUERNSEY = 'GG';

    protected const SUPPORTS_DOMESTIC_SHIPPING = [
        self::UNITED_KINGDOM => true,
        self::GREAT_BRITAIN => true,
        self::ISLE_OF_MAN => true,
        self::JERSEY => true,
        self::GUERNSEY => true,
    ];

    protected const CN23_DOCUMENTATION_REQUIRED = [
        self::UNITED_KINGDOM => false,
        self::GREAT_BRITAIN => false,
        self::ISLE_OF_MAN => false,
        self::JERSEY => true,
        self::GUERNSEY => true,
    ];

    public static function countrySupportsDomesticShipping(string $iso2CountryCode): bool
    {
        $sanitisedIso2CountryCode = strtoupper($iso2CountryCode);
        if (!isset(static::SUPPORTS_DOMESTIC_SHIPPING[$sanitisedIso2CountryCode])) {
            return false;
        }
        return static::SUPPORTS_DOMESTIC_SHIPPING[$sanitisedIso2CountryCode];
    }

    public static function countryRequiresCn23Documentation(string $iso2CountryCode): bool
    {
        $sanitisedIso2CountryCode = strtoupper($iso2CountryCode);
        if (!isset(static::CN23_DOCUMENTATION_REQUIRED[$sanitisedIso2CountryCode])) {
            return true;
        }
        return static::CN23_DOCUMENTATION_REQUIRED[$sanitisedIso2CountryCode];
    }
}