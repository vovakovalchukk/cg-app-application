<?php
namespace NewRelic\Api\Event;

use Zend\Mvc\MvcEvent;
use CG\User\ActiveUserInterface;

class RegisterUser
{
    protected $activeUserContainer;

    public function __construct(ActiveUserInterface $activeUserContainer)
    {
        $this->setActiveUserContainer($activeUserContainer);
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function __invoke(MvcEvent $event)
    {
        $user = $this->getActiveUserContainer()->getActiveUser();
        if (!$user) {
            return;
        }

        newrelic_set_user_attributes(
            $user->getId(),
            '',
            ''
        );

        newrelic_add_custom_parameter('userId', $user->getId());
    }
} 