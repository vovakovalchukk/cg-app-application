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

$componentArray = [];
foreach ($componentTypes as $type => $components) {
    foreach ($components as $component) {
        $diCompiler = new Zend\Di\Definition\CompilerDefinition;
        $dir = dirname(__DIR__) . '/' . $type . '/' . str_replace('_', '/', $component);
        echo "Compiling ".$dir."\n";
        $diCompiler->addDirectory($dir);
        $diCompiler->setAllowReflectionExceptions();
        $diCompiler->compile();
        $componentArray = array_merge($componentArray, $diCompiler->toArrayDefinition()->toArray());
    }
}
file_put_contents(
    $diDataDir .'di-definition.php',
    '<?php return ' . var_export($componentArray, true) . ';'
);