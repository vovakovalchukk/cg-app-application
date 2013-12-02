<?php
require_once 'bootstrap.php';
require_once 'config/di/components.php';

$diDataDir = 'data/di/';

$it = new DirectoryIterator($diDataDir);
foreach ($it as $file) {
    if ($file->isFile() && !$file->isLink()
        && preg_match('/-definition.php$/', $file->getBasename(), $matches)) {
        unlink($file->getPathname());
    }
}

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