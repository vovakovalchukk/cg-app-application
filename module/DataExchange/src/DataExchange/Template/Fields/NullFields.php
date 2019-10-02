<?php
namespace DataExchange\Template\Fields;

use DataExchange\Template\FieldsInterface;

class NullFields implements FieldsInterface
{
    public static function getFields(): array
    {
        return [];
    }
}
