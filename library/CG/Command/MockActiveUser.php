<?php
namespace CG\Command;

use CG\User\ActiveUserInterface;
use CG\User\Entity as User;

class MockActiveUser implements ActiveUserInterface
{
    public function getActiveUser()
    {
        // TODO: Implement getActiveUser() method.
    }

    public function setActiveUser(User $activeUser)
    {
        // TODO: Implement setActiveUser() method.
    }

    public function getActiveUserRootOrganisationUnitId()
    {
        // TODO: Implement getActiveUserRootOrganisationUnitId() method.
    }

    public function isAdmin()
    {
        // TODO: Implement isAdmin() method.
    }

    public function getCompanyId()
    {
        // TODO: Implement getCompanyId() method.
    }

    public function getTimezone(): string
    {
        // TODO: Implement getTimezone() method.
    }

    public function setTimezone(string $timezone)
    {
        // TODO: Implement setTimezone() method.
    }

    public function getLocale(): string
    {
        // TODO: Implement getLocale() method.
    }

    public function setLocale(string $locale)
    {
        // TODO: Implement setLocale() method.
    }
}