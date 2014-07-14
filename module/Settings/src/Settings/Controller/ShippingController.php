<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingService;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";

    protected $viewModelFactory;
    protected $shippingService;

    public function __construct(ViewModelFactory $viewModelFactory, ShippingService $shippingService)
    {
        $this->setViewModelFactory($viewModelFactory)
            ->setShippingService($shippingService);
    }

    public function aliasAction()
    {
        $shippingMethods = $this->getShippingService()->fetchMethods();
        $view = $this->getViewModelFactory()->newInstance();
        $view->setVariable('title', static::ROUTE_ALIASES);
        $view->setVariable('shippingMethods', $shippingMethods->toArray());
        $view->addChild($this->getAddButtonView(), 'addButton');
        return $view;
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

    protected function getShippingService()
    {
        return $this->shippingService;
    }

    protected function setShippingService(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }
}