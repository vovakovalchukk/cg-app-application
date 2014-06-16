<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ExceptionInterface;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;

class IndexController extends AbstractActionController implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator = null)
    {
        if ($serviceLocator) {
            $this->setServiceLocator($serviceLocator);
        }
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function indexAction()
    {
        try {
        $this->redirect()->toRoute('Orders');
        } catch (\Exception $e) {
            exit('foo');
        }
    }
}
