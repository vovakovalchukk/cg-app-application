<?php
namespace Orders\Courier;

use CG\Account\Shared\Collection as AccountCollection;

/**
 * @deprecated Use ShippingAccountsService instead
 */
trait GetShippingAccountOptionsTrait
{
    /**
     * @return array
     */
    public function getShippingAccountOptions($selectedAccountId = null)
    {
        $shippingAccounts = $this->getShippingAccounts();
        return $this->convertShippingAccountsToOptions($shippingAccounts, $selectedAccountId);
    }

    protected function convertShippingAccountsToOptions(AccountCollection $shippingAccounts, $selectedAccountId = null)
    {
        $courierOptions = [];
        foreach ($shippingAccounts as $shippingAccount) {
            $courierOptions[] = [
                'value' => $shippingAccount->getId(),
                'title' => $shippingAccount->getDisplayName(),
                'selected' => ($shippingAccount->getId() == $selectedAccountId),
            ];
        }
        return $courierOptions;
    }

    abstract protected function getShippingAccounts();
}
