<?php
define('GREEN', "\033[32m");
define('WHITE', "\033[0m");

$frameworkComponents = require dirname(__DIR__) . '/config/di/framework_components.php';
$phpInternalComponents = require dirname(__DIR__) . '/config/di/php_internal_components.php';
$vendorComponents = array_merge($frameworkComponents, require dirname(__DIR__) . '/config/di/vendor_components.php');

$componentTypes = [
    'module' => [
        'Application_src_Application_Controller'
    ],
    'vendor' => $vendorComponents
];

require_once 'bootstrap.php';

echo GREEN . 'Compiling DI definitions' . WHITE . PHP_EOL;

$diDataDir = 'data/di/';

$it = new DirectoryIterator($diDataDir);
foreach ($it as $file) {
    if ($file->isFile() && !$file->isLink()
        && preg_match('/-definition.php$/', $file->getBasename(), $matches)) {
        unlink($file->getPathname());
    }
}

$componentArray = [];

foreach ($phpInternalComponents as $class) {
    $diCompiler = new CG\Zend\Stdlib\Di\Definition\RuntimeCompiler;
    echo "Compiling inbuilt ".$class."\n";
    $diCompiler->compileClass($class);
    $componentArray = array_merge($componentArray, $diCompiler->toArrayDefinition()->toArray());
}

foreach ($componentTypes as $type => $components) {
    foreach ($components as $component) {
        $diCompiler = new CG\Zend\Stdlib\Di\Definition\RuntimeCompiler;
        $dir = dirname(__DIR__) . '/' . $type . '/' . stripslashes(preg_replace('|(?<!\\\\)_|', '/', $component));
        echo "Compiling ".$type." ".$dir."\n";
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

echo 'DONE' . PHP_EOL;
