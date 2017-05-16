<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;

class LinksJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'Links AJAX';

    protected $jsonModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function ajaxAction()
    {
        try {
            $sku = $this->params()->fromPost('sku');

            /**
             * retrieve product links
             */
            $linkedProducts = [];
        } catch(NotFound $e) {
            //noop
        }

        return $this->jsonModelFactory->newInstance([
            'linkedProducts' => $linkedProducts
        ]);
    }
}
