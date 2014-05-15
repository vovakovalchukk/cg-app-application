<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class InvoiceController extends AbstractActionController
{
    const ROUTE = 'Invoice';

    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->setViewModelFactory($viewModelFactory);
    }

    public function designAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->addChild($this->getPaperTypeModule(), 'paperTypeModule');

        return $view;
    }

    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getPaperTypeModule()
    {
        $dropDownConfig = [
            "isOptional" => false,
            "id" => "paperTypeDropdown",
            "name" => "paperTypeDropdown",
            "class" => "",
            "options" => []
        ];
        $dropDown = $this->getViewModelFactory()->newInstance($dropDownConfig);
        $dropDown->setTemplate('elements/custom-select');

        $paperTypeModule = $this->getViewModelFactory()->newInstance();
        $paperTypeModule->setTemplate('InvoiceDesigner/Template/paperType');
        $paperTypeModule->addChild($dropDown, 'dropDown');

        return $paperTypeModule;
    }
}
