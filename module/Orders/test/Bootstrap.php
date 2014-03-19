<?php
require dirname(dirname(dirname(__DIR__))) . '/AbstractBootstrap.php';

class Bootstrap extends AbstractBootstrap
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