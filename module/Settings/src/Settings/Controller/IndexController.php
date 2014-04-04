<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    const ROUTE = "Channel Management";

    public function indexAction()
    {
        $this->redirect()->toRoute('Channel Management/Sales Channels');
    }
}