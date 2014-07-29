<?php

namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ProductsJsonController extends AbstractActionController
{
    const AJAX_ROUTE = 'AJAX';

    public function construct()
    {

    }

    public function ajaxAction()
    {
        echo 'hello!';
    }
}