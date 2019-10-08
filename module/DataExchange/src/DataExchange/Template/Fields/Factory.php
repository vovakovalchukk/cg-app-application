<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class Factory
{
    public static function fetchFieldsForType(string $type): array
    {
        /** @var FieldsInterface $class */
        $class = __NAMESPACE__ . '\\' . ucfirst(strtolower($type));
        if (!class_exists($class)) {
            return NullFields::getFields();
        }

        return $class::getFields();
    }
}
