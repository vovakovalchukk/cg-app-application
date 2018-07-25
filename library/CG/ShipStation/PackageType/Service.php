<?php

namespace CG\ShipStation\PackageType;

class Service
{
    /** @var array */
    protected $packageTypes;

    public function __construct(array $packageTypes)
    {
        $this->packageTypes = $packageTypes;
    }
}