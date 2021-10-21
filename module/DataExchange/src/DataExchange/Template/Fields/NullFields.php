<?php
namespace DataExchange\Template\Fields;

use CG\OrganisationUnit\Entity as Ou;
use DataExchange\Template\FieldsInterface;

class NullFields implements FieldsInterface
{
    public static function getFields(Ou $ou): array
    {
        return [];
    }
}
