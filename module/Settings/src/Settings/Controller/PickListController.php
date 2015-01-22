<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Settings\PickList\Service as PickListService;

class PickListController extends AbstractActionController
{
    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $pickListService;

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        PickListService $pickListService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setPickListService($pickListService);
    }

    /**
     * @return PickListService
     */
    protected function getPickListService()
    {
        return $this->pickListService;
    }

    /**
     * @param PickListService $pickListService
     * @return $this
     */
    public function setPickListService(PickListService $pickListService)
    {
        $this->pickListService = $pickListService;
        return $this;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param JsonModelFactory $jsonModelFactory
     * @return $this
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @param ViewModelFactory $viewModelFactory
     * @return $this
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}