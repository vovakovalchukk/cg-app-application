<?php
namespace Orders\Order\Filter;

use CG_UI\View\Filters\Options\Select;
use CG\Stdlib\Exception\Runtime\NotFound;

class Account extends Channel
{
    /**
     * @return Select[] array of options to be added to filter
     */
    public function getOptions()
    {
        $options = [];
        try {
            $accounts = $this->getAccounts($this->getActiveUser());
            foreach ($accounts as $account) {
                $options[] = $this->getDi()->newInstance(
                    Select::class,
                    [
                        'title' => htmlentities($account->getDisplayName(), ENT_QUOTES),
                    ]
                );
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
} 