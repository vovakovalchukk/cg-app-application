<?php
namespace CG\Account\Shipping;

use CG\Account\Shared\Entity as Account;

interface GenericAccountProviderInterface
{
    public function __invoke(): ?Account;
}