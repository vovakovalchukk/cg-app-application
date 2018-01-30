<?php
namespace CG\ShipStation\Carrier\Field;

use CG\ShipStation\Carrier\Field;

class Mapper
{
    public function fromArray(array $fieldConfig): Field
    {
        return new Field(
            $fieldConfig['name'],
            $fieldConfig['label'],
            $fieldConfig['required'] ?? null,
            $fieldConfig['inputType'] ?? null,
            $fieldConfig['value'] ?? null
        );
    }

    public function collectionFromArray(array $fieldsConfig): Collection
    {
        $collection = new Collection(Field::class, __FUNCTION__);
        foreach ($fieldsConfig as $fieldConfig) {
            $collection->attach($this->fromArray($fieldConfig));
        }
        return $collection;
    }
}