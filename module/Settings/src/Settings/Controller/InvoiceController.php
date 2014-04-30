<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class InvoiceController extends AbstractActionController
{
    const ROUTE = 'Invoice';

    public function __construct()
    {

    }

    public function designAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        return $view;
    }
}
