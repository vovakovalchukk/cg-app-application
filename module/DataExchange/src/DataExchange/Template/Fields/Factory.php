<?php
namespace DataExchange\Template\Fields;

use CG\OrganisationUnit\Entity as Ou;
use DataExchange\Template\FieldsInterface;

class Factory
{
    public static function fetchFieldsForType(string $type, Ou $ou): array
    {
        /** @var FieldsInterface $class */
        $class = __NAMESPACE__ . '\\' . ucfirst($type);
        if (!class_exists($class)) {
            return NullFields::getFields($ou);
        }

        return $class::getFields($ou);
    }
}
