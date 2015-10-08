<?php
namespace Filters\Options;

use CG_UI\View\Filters\SelectOptionsInterface;

class Marketplace implements SelectOptionsInterface
{
    /**
     * @return array key => value pairs to be added to select filter options
     */
    public function getSelectOptions()
    {
        // TODO: Return avaliable marketplaces
        return [];
    }
} 
