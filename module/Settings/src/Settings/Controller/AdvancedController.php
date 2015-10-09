<?php
namespace Settings\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;

abstract class AdvancedController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE = 'Advanced';

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE.'/' . ApiController::ROUTE_API);
    }
} 
