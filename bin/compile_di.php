<?php
define('GREEN', "\033[32m");
define('WHITE', "\033[0m");

require_once 'bootstrap.php';
require_once 'config/di/components.php';

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
foreach ($componentTypes as $type => $components) {
    foreach ($components as $component) {
        $diCompiler = new CG\Zend\Stdlib\Di\Definition\RuntimeCompiler;
        $dir = dirname(__DIR__) . '/' . $type . '/' . stripslashes(preg_replace('|(?<!\\\\)_|', '/', $component));
        echo $dir.PHP_EOL;
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
