<?php

require dirname(dirname(dirname(__DIR__))) . '/AbstractBootstrap.php';
//chdir(__DIR__);

class Bootstrap extends AbstractBootstrap
{
    public function getModuleDependencies()
    {
        return array(
//            'Mustache',
            'CG_UI',
            'Orders'
        );
    }
}
Bootstrap::init();