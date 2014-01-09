<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;

class OrdersController extends AbstractActionController
{
    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->setJsonModelFactory($jsonModelFactory);
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

    public function listAction()
    {
        return $this->getJsonModelFactory()->newInstance();
    }
}