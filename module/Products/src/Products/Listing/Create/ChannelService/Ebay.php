<?php
namespace Products\Listing\Create\ChannelService;

use CG\Account\Shared\Entity as Account;

class Ebay implements ServiceInterface
{
    const ALLOWED_SETTINGS_KEYS = [
        'listingLocation' => 'listingLocation',
        'listingCurrency' => 'listingCurrency',
        'paypalEmail' => 'paypalEmail',
        'listingDuration' => 'listingDuration',
        'listingDispatchTime' => 'listingDispatchTime',
        'listingPaymentMethods' => 'listingPaymentMethods'
    ];

    public function getDefaultSettingsForAccount(Account $account): array
    {
        return $this->filterDefaultSettingsKeys($account->getExternalData());
    }

    protected function filterDefaultSettingsKeys(array $data)
    {
        return array_filter(
            $data,
            function($key) {
                return isset(static::ALLOWED_SETTINGS_KEYS[$key]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
