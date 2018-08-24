<?php
namespace CG\ShipStation\PackageType\RoyalMail;

class Service
{
    /** @var Collection */
    protected $domestic;
    /** @var Collection */
    protected $international;

    public function __construct(array $domesticConfig, array $internationalConfig)
    {
        $this->domestic = new Collection(Entity::class, __CLASS__);
        foreach ($domesticConfig as $config) {
            $this->domestic->attach(Entity::fromArray($config));
        }
        $this->international = new Collection(Entity::class, __CLASS__);
        foreach ($internationalConfig as $config) {
            $this->international->attach(Entity::fromArray($config));
        }
    }


}