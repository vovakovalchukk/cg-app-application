<?php
namespace CG\ShipStation\Request\Partner\Account;

trait ExternalIdTrait
{
    protected static function generateExternalId(string $externalAccountId): string
    {
        // Prefix with environment as these have to be unique and we don't want dev/qa taking up real OU IDs
        return ENVIRONMENT . '-' . $externalAccountId;
    }
}