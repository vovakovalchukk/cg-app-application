<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\Settings\Alias\Service as ShippingService;
use CG\User\ActiveUserInterface;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";
    const ROUTE_ALIASES_SAVE = 'Shipping Alias Save';

    protected $viewModelFactory;
    protected $conversionService;
    protected $shippingService;
    protected $jsonModelFactory;
    protected $activeUser;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ConversionService $conversionService,
        ShippingService $shippingService,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUser
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setConversionService($conversionService)
            ->setShippingService($shippingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUser($activeUser);
    }

    public function aliasAction()
    {
        $shippingMethods = $this->getConversionService()->fetchMethods();
        $view = $this->getViewModelFactory()->newInstance();
        $view->setVariable('title', static::ROUTE_ALIASES);
        $view->setVariable('shippingMethods', $shippingMethods->toArray());
        $view->setVariable('rootOuId', $this->getActiveUser()->getActiveUserRootOrganisationUnitId());
        $view->addChild($this->getAddButtonView(), 'addButton');
        return $view;
    }

    public function aliasSaveAction()
    {
        $alias = $this->getShippingService()->saveFromJson($this->params()->fromPost('alias'));
        return $this->getJsonModelFactory()->newInstance(["alias" => json_encode($alias)]);
    }

    protected function getAddButtonView()
    {
        $button = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->translate("Add"),
            'id' => "addButtonSelector"
        ]);
        $button->setTemplate('elements/buttons.mustache');
        return $button;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getConversionService()
    {
        return $this->conversionService;
    }

    protected function setConversionService(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
        return $this;
    }

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    protected function setJsonModelFactory($jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setActiveUser(ActiveUserInterface $activeUser)
    {
        $this->activeUser = $activeUser;
        return $this;
    }

    protected function getActiveUser()
    {
        return $this->activeUser;
    }
}