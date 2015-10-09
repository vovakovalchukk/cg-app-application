<?php
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

abstract class AdvancedController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE = 'Advanced';
} 
