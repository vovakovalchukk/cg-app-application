<?php
namespace DataExchange\Navigation;

use Zend\Navigation\Service\DefaultNavigationFactory;

class Factory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'data-exchange-navigation';
    }
}
