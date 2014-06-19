<?php
namespace Settings\Factory;

class SidebarNav extends \Zend\Navigation\Service\DefaultNavigationFactory
{
    protected function getName()
    {
        return 'sidebar-nav';
    }
}