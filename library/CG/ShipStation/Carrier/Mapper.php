<?php
namespace CG\ShipStation\Carrier;

use CG\ShipStation\Carrier\Field\Mapper as FieldMapper;

class Mapper
{
    /** @var FieldMapper */
    protected $fieldMapper;

    public function __construct(FieldMapper $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    public function fromArray(array $carrierConfig): Entity
    {
        return new Entity(
            $carrierConfig['channelName'],
            $this->fieldMapper->collectionFromArray($carrierConfig['fields']),
            $carrierConfig['displayName'] ?? null,
            $carrierConfig['salesChannelName'] ?? null,
            $carrierConfig['allowsCancellation'] ?? null,
            $carrierConfig['allowsManifesting'] ?? null
        );
    }

    public function collectionFromArray(array $carriersConfig): Collection
    {
        $collection = new Collection(Entity::class, __FUNCTION__);
        foreach ($carriersConfig as $carrierConfig) {
            $collection->attach($this->fromArray($carrierConfig));
        }
        return $collection;
    }
}