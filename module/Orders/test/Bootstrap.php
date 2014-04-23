<?php
require dirname(dirname(dirname(__DIR__))) . '/BootstrapAbstract.php';

class Bootstrap extends BootstrapAbstract
{
    public static function getModuleDependencies()
    {
        return array(
            'Mustache',
            'CG_UI',
            'Orders'
        );
    }
}
Bootstrap::init();