<?php
namespace CG\ShipStation\Request;

use CG\ShipStation\RequestAbstract;

abstract class PartnerRequestAbstract extends RequestAbstract
{
    protected const URI_PREFIX = '/partners';

    public function getUri(): string
    {
        return static::URI_VERSION_PREFIX . static::URI_PREFIX . static::URI;
    }
}
