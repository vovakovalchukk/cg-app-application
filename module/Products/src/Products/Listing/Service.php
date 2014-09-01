<?php
namespace Products\Listing;

use CG\User\ActiveUserInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UserPreference\Client\Service as UserPreferenceService;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LISTING_FILTER_BAR_STATE_KEY = 'listing-filter-bar-state';

    protected $activeUserContainer;
    protected $userPreferenceService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService);
    }

    public function isFilterBarVisible()
    {
        $preference = $this->getActiveUserPreference()->getPreference();
        $visible = isset($preference[static::LISTING_FILTER_BAR_STATE_KEY]) ? $preference[static::LISTING_FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    protected function getActiveUserPreference()
    {
        if (!isset($this->activeUserPreference)) {
            $activeUserId = $this->getActiveUser()->getId();
            $this->activeUserPreference = $this->getUserPreferenceService()->fetch($activeUserId);
        }

        return $this->activeUserPreference;
    }

    protected function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    protected function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    protected function getUserPreferenceService()
    {
        return $this->userPreferenceService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}