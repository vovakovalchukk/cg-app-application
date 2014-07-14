<?php

namespace Settings\Factory;

use Zend\Navigation\Service\DefaultNavigationFactory;

class SidebarNavFactory extends DefaultNavigationFactory
{

    protected function getName()
    {
        return 'sidebar-navigation';
    }
}