<?php
namespace DataExchange\Template;

use CG\OrganisationUnit\Entity as Ou;

interface FieldsInterface
{
    public static function getFields(Ou $ou): array;
}
