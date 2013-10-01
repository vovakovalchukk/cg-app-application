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
        $view = new ViewModel();
        $view->setVariable('framework', 'ZF2');

        $serviceLocator = $this->getServiceLocator();
        try {
            $dbAdapter = $serviceLocator->get('readDb');
            $view->setVariable('db', $dbAdapter->getCurrentSchema());
            $view->setVariable('tables', $dbAdapter->query('SHOW TABLES', Adapter::QUERY_MODE_EXECUTE));
        }
        catch (ExceptionInterface $exception) {
            // If no Db Adapter - Application created without database
        }

        return $view;
    }
}
