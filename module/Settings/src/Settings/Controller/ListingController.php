<?php
namespace Settings\Controller;
use Zend\Mvc\Controller\AbstractActionController;

class ListingController extends AbstractActionController
{

    const ROUTE = "Listing";


    public function __construct(
    ) {
    }

    public function indexAction()
    {
//        return $this->redirect()->toRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_CHANNELS, ['type' => Type::SALES]);
    }


}
