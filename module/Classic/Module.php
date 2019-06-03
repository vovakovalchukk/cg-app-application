<?php
namespace Classic;

use Zend\Config\Factory as ConfigFactory;

class Module
{
    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
    }
}