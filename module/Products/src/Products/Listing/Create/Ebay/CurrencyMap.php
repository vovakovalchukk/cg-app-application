<?php
namespace Products\Listing\Create\Ebay;

use CG\Ebay\Site\Map as SiteMap;

class CurrencyMap
{
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';
    const CURRENCY_AUD = 'AUD';
    const CURRENCY_CAD = 'CAD';
    const CURRENCY_CHF = 'CHF';
    const CURRENCY_HKD = 'HKD';
    const CURRENCY_INR = 'INR';
    const CURRENCY_MYR = 'MYR';
    const CURRENCY_PHP = 'PHP';
    const CURRENCY_PLN = 'PLN';
    const CURRENCY_SEK = 'SEK';
    const CURRENCY_SGD = 'SGD';
    const CURRENCY_TWD = 'TWD';

    public static function getCurrencyCodeMap(): array
    {
        return [
            SiteMap::SITE_CODE_AUSTRALIA         => static::CURRENCY_AUD,
            SiteMap::SITE_CODE_AUSTRIA           => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_BELGIUM_DUTCH     => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_BELGIUM_FRENCH    => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_CANADA            => static::CURRENCY_CAD,
            SiteMap::SITE_CODE_CANADA_FRENCH     => static::CURRENCY_CAD,
            SiteMap::SITE_CODE_FRANCE            => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_GERMANY           => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_HONGKONG          => static::CURRENCY_HKD,
            SiteMap::SITE_CODE_INDIA             => static::CURRENCY_INR,
            SiteMap::SITE_CODE_IRELAND           => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_ITALY             => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_MALAYSIA          => static::CURRENCY_MYR,
            SiteMap::SITE_CODE_NETHERLANDS       => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_PHILIPPINES       => static::CURRENCY_PHP,
            SiteMap::SITE_CODE_POLAND            => static::CURRENCY_PLN,
            SiteMap::SITE_CODE_SINGAPORE         => static::CURRENCY_SGD,
            SiteMap::SITE_CODE_SPAIN             => static::CURRENCY_EUR,
            SiteMap::SITE_CODE_SWITZERLAND       => static::CURRENCY_CHF,
            SiteMap::SITE_CODE_UK                => static::CURRENCY_GBP,
            SiteMap::SITE_CODE_US                => static::CURRENCY_USD
        ];
    }

    public static function getCurrencySymbolMap(): array
    {
        return [
            static::CURRENCY_GBP => '£',
            static::CURRENCY_EUR => '€',
            static::CURRENCY_USD => '$',
            static::CURRENCY_AUD => '$',
            static::CURRENCY_CAD => '$',
            static::CURRENCY_CHF => static::CURRENCY_CHF,
            static::CURRENCY_HKD => '$',
            static::CURRENCY_INR => static::CURRENCY_INR,
            static::CURRENCY_MYR => 'RM',
            static::CURRENCY_PHP => '₱',
            static::CURRENCY_PLN => 'zł',
            static::CURRENCY_SEK => 'kr',
            static::CURRENCY_SGD => '$',
            static::CURRENCY_TWD => 'NT$'
        ];
    }

    public static function getCurrencyCodeBySiteId(int $siteId): string
    {
        if (!isset(static::getCurrencyCodeMap()[$siteId])) {
            throw new \InvalidArgumentException('No currency code found for the provided site Id: ' . $siteId);
        }
        return static::getCurrencyCodeMap()[$siteId];
    }

    public static function getCurrencySymbolBySiteId(int $siteId): string
    {
        if (!isset(static::getCurrencyCodeMap()[$siteId])) {
            throw new \InvalidArgumentException('No currency code found for the provided site Id: ' . $siteId);
        }
        $currencyCode = static::getCurrencyCodeMap()[$siteId];
        if (!isset(static::getCurrencySymbolMap()[$currencyCode])) {
            throw new \InvalidArgumentException('No currency symbol found for currency with code: ' . $currencyCode);
        }
        return static::getCurrencySymbolMap()[$currencyCode];
    }

    public static function getCurrencySymbolByCurrencyCode(string $currencyCode): string
    {
        if (!isset(static::getCurrencySymbolMap()[$currencyCode])) {
            throw new \InvalidArgumentException('No currency symbol found for currency with code: ' . $currencyCode);
        }
        return static::getCurrencySymbolMap()[$currencyCode];
    }
}
