<?php
require_once 'bootstrap.php';
require_once 'config/di/components.php';

if (!isset($argv[1]) || !isset($argv[2])) {
    echo 'Component type and name must be passed in'.PHP_EOL;
    exit(1);
}
$type = $argv[1];
$component = $argv[2];

$diCompiler = new Zend\Di\Definition\CompilerDefinition;
$dir = dirname(__DIR__) . '/' . $type . '/' . str_replace('_', '/', $component);
echo "Compiling ".$dir."\n";
$diCompiler->addDirectory($dir);
$diCompiler->setAllowReflectionExceptions();
$diCompiler->compile();

exit(0);