<?php
namespace Products\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class StockLogJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->setJsonModelFactory($jsonModelFactory);
    }

    public function ajaxAction()
    {
        $data = [];
// TODO

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }
}