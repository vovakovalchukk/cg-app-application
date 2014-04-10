<?php
namespace Application\Controller;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\ControllerManager;
use BadMethodCallException;

class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @throws BadMethodCallException
     * @return ServiceLocatorInterface
     */
    protected function getServiceManager(ServiceLocatorInterface $serviceLocator)
    {
        if (!($serviceLocator instanceof ControllerManager)) {
            throw new BadMethodCallException(
                'This abstract factory is meant to be used only with a controller manager'
            );
        }

        return $serviceLocator->getServiceLocator();
    }

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return class_exists($requestedName);
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->getServiceManager($serviceLocator)->get($requestedName);
    }
}