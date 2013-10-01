<?php
require_once 'bootstrap.php';
require_once 'config/di/components.php';

$diDataDir = 'data/di/';
if (is_dir($diDataDir)) {
    CG\Stdlib\rm($diDataDir);
}
mkdir($diDataDir, 0777, true);

foreach ($libraryComponents as $component) {
    $diCompiler = new Zend\Di\Definition\CompilerDefinition;
    $dir = dirname(__DIR__) . '/library/' . str_replace('_', '/', $component);
    echo "Compiling ".$dir."\n";
    $diCompiler->addDirectory($dir);
    $diCompiler->setAllowReflectionExceptions();
    $diCompiler->compile();
    file_put_contents(
        $diDataDir . $component . '-definition.php',
        '<?php return ' . var_export($diCompiler->toArrayDefinition()->toArray(), true) . ';'
    );
}

foreach ($vendorComponents as $component) {
    $diCompiler = new Zend\Di\Definition\CompilerDefinition;
    $dir = dirname(__DIR__) . '/vendor/' . str_replace('_', '/', $component);
    echo "Compiling ".$dir."\n";
    $diCompiler->addDirectory($dir);
    $diCompiler->setAllowReflectionExceptions(true);
    $diCompiler->compile();
    file_put_contents(
        $diDataDir . $component . '-definition.php',
        '<?php return ' . var_export($diCompiler->toArrayDefinition()->toArray(), true) . ';'
    );
}