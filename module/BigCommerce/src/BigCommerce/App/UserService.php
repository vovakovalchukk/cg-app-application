<?php
namespace BigCommerce\App;

use CG\User\Entity as OHUser;
use CG\User\Service as OHUserService;
use Predis\Client as Predis;

class UserService
{
    const CACHE_KEY = 'BigCommerce:Users';

    /** @var Predis $predis */
    protected $predis;
    /** @var OHUserService $userService */
    protected $userService;

    public function __construct(Predis $predis, OHUserService $userService)
    {
        $this->setPredis($predis)->setUserService($userService);
    }

    public function registerUserAssociation($userId, OHUser $user)
    {
        $this->predis->hset(static::CACHE_KEY, $userId, $user->getId());
    }

    /**
     * @return OHUser
     */
    public function getAssociatedUser($userId)
    {
        $associatedId = $this->predis->hget(static::CACHE_KEY, $userId);
        return $this->userService->fetch($associatedId);
    }

    /**
     * @return self
     */
    protected function setPredis(Predis $predis)
    {
        $this->predis = $predis;
        return $this;
    }

    /**
     * @return self
     */
    protected function setUserService(OHUserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }
}
