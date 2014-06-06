<?php
namespace Orders\Order\Filter;

use CG\Stdlib\Exception\Runtime\NotFound;

class Account extends Channel
{
    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [];
        try {
            $accounts = $this->getAccounts($this->getActiveUser());
            foreach ($accounts as $account) {
                $options[$account->getId()] = $account->getDisplayName();
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
} 