<?php
namespace CG\Command;

use CG\User\ActiveUserInterface;
use CG\User\Entity as User;

class NullActiveUser implements ActiveUserInterface
{
    public function getActiveUser()
    {
        // No-op
    }

    public function setActiveUser(User $activeUser)
    {
        // No-op
    }

    public function getActiveUserRootOrganisationUnitId()
    {
        // No-op
    }

    public function isAdmin()
    {
        // No-op
    }

    public function getCompanyId()
    {
        // No-op
    }

    public function getTimezone(): string
    {
        // No-op
    }

    public function setTimezone(string $timezone)
    {
        // No-op
    }

    public function getLocale(): string
    {
        // No-op
    }

    public function setLocale(string $locale)
    {
        // No-op
    }
}