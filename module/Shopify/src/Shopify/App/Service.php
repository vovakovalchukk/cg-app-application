<?php
namespace Shopify\App;

use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUser;

    public function __construct(ActiveUserInterface $activeUser)
    {
        $this->activeUser = $activeUser;
    }

    public function processOauth($redirectUri, array $parameters)
    {
        if (!$this->activeUser->getActiveUser()) {
            throw new LoginException('User is not logged in');
        }
    }
}