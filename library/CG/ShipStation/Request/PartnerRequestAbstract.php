<?php
namespace CG\ShipStation\Request;

use CG\ShipStation\RequestAbstract;

abstract class PartnerRequestAbstract extends RequestAbstract
{
    protected const URI_PREFIX = '/partners';

    public function getUri(): string
    {
        return static::API_VERSION . static::URI_PREFIX . static::URI;
    }
}
