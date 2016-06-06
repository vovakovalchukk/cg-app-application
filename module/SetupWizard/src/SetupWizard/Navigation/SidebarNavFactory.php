<?php
namespace SetupWizard\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class SidebarNavFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'setup-navigation';
    }
}