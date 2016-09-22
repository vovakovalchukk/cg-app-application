<?php
namespace Products\Listing\Filter\Options;

use CG\Listing\Unimported\Status as UnimportedListingStatus;
use CG_UI\View\Filters\SelectOptionsInterface;

class Status implements SelectOptionsInterface
{
    /**
     * @inheritDoc
     */
    public function getSelectOptions()
    {
        $selectOptions = [];
        foreach (array_merge(UnimportedListingStatus::getAllStatuses(), UnimportedListingStatus::getErrorStatuses()) as $status) {
            $selectOptions[$status] = ucwords(str_replace('_', ' ', $status));
        }
        return $selectOptions;
    }
}
