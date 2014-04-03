<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->redirect()->toRoute('Channel Management/Sales Channels');
    }
}