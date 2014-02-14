<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\User\ActiveUserInterface;
use CG\Stdlib\Exception\Runtime\RequiredKeyMissing;

class PreferenceController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $userPreferenceService;
    protected $activeUserContainer;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        UserPreferenceService $userPreferenceService
    )
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setUserPreferenceService($userPreferenceService);
    }

    public function saveAction()
    {
        $response = $this->getJsonModelFactory()->newInstance();
        $userId = $this->getActiveUserContainer()->getActiveUser()->getId();
        $key = $this->params()->fromPost('key');
        $value = $this->params()->fromPost('value');
        try {
            $this->getUserPreferenceService()->savePartial($userId, $key, $value);
        } catch (RequiredKeyMissing $e) {
            return $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }

    public function setUserPreferenceService(UserPreferenceService $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
        return $this;
    }

    public function getUserPreferenceService()
    {
        return $this->userPreferenceService;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setActiveUserContainer($activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}