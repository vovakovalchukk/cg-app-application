<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Orders;

use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module implements DependencyIndicatorInterface
{
    const PUBLIC_FOLDER = '/channelgrabber/orders/';

    protected $header;

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
        $this->renderNavBar($viewModel);
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
     * @param ViewModel $layout
     * @return ViewModel
     */
    protected function getHeaderViewModel(ViewModel $layout)
    {
        if ($this->header) {
            return $this->header;
        }

        if (!$layout->hasChildren()) {
            return;
        }

        $this->header = null;
        foreach ($layout->getChildren() as $child) {
            if ($child->captureTo() == 'header') {
                $this->header = $child;
                break;
            }
        }
        return $this->header;
    }

    protected function renderNavBar(ViewModel $layout)
    {
        $header = $this->getHeaderViewModel($layout);
        if (!$header) {
            return;
        }

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
