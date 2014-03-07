<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Orders;

use Zend\Di\Di;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use CG_UI\Layout\ViewModelFactory;

class Module implements DependencyIndicatorInterface
{
    const PUBLIC_FOLDER = '/channelgrabber/orders/';

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
    }

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getModuleDependencies()
    {
        return [
            'CG_UI'
        ];
    }

    public function layoutHandler(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        if (!($viewModel instanceof ViewModel)) {
            return;
        }
        $this->renderBodyTag($viewModel);
        $this->renderNavBar($event, $viewModel);
    }

    protected function renderBodyTag(ViewModel $layout)
    {
        $layout->addChild($this->getBodyTagViewModel(), 'bodyTag', true);
    }

    /**
     * @return ViewModel
     */
    protected function getBodyTagViewModel()
    {
        $bodyTag = new ViewModel();
        $bodyTag->setTemplate('orders/orders/bodyTag');
        return $bodyTag;
    }

    /**
     * @param MvcEvent $event
     * @return ViewModel
     */
    protected function getHeaderViewModel(MvcEvent $event)
    {
        $di = $event->getApplication()->getServiceManager()->get(Di::class);
        $viewModelFactory = $di->get(ViewModelFactory::class);
        return $viewModelFactory->get('header');
    }

    protected function renderNavBar(MvcEvent $event, ViewModel $layout)
    {
        $header = $this->getHeaderViewModel($event);
        foreach ($this->getNavBarItems() as $navBarItem) {
            $header->addChild($navBarItem, 'navBar', true);
        }
    }

    /**
     * @return ViewModel[]
     */
    protected function getNavBarItems()
    {
        $navBarItemParameters = [
            [
                'class' => 'orders',
                'route' => 'Orders',
                'parameters' => [],
                'text' => 'orders'
            ]
        ];

        $navBarItems = [];
        foreach ($navBarItemParameters as $navBarItemParameter) {
            $navBarItemViewModel = new ViewModel($navBarItemParameter);
            $navBarItemViewModel->setTemplate('orders/orders/navBarItem');
            $navBarItems[] = $navBarItemViewModel;
        }
        return $navBarItems;
    }
}
