<?php
namespace Orders\Courier;

trait GetShippingAccountOptionsTrait
{
    /**
     * @return array
     */
    public function getShippingAccountOptions()
    {
        $shippingAccounts = $this->getShippingAccounts();
        $courierOptions = [];
        foreach ($shippingAccounts as $shippingAccount) {
            $courierOptions[] = [
                'value' => $shippingAccount->getId(),
                'title' => $shippingAccount->getDisplayName(),
            ];
        }
        return $courierOptions;
    }

    abstract protected function getShippingAccounts();
}