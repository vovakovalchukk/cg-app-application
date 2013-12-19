<?php
define('YELLOW', "\033[33m");
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('WHITE', "\033[0m");
define('COMPONENTS_FILE', 'config/di/vendor_components.php');

if (!is_writable(COMPONENTS_FILE)) {
    echo RED . 'Cannot update DI components as ' . COMPONENTS_FILE . ' is not writeable. Check the file permissions or try sudo-ing' . WHITE . PHP_EOL;
    exit(1);
}

require_once 'bootstrap.php';

echo GREEN . 'Updating DI components' . WHITE . PHP_EOL;

$vendorExclusions = ['bin', 'composer', 'phpunit'];
$projectExclusions = [
    'facebook' => [
        'php-sdk'
    ]
];
$vendorDir = 'vendor/';
$currentComponents = require COMPONENTS_FILE;
$newComponents = [];

$vendors = new DirectoryIterator($vendorDir);
foreach ($vendors as $vendor) {
    $vendorName = $vendor->getFilename();
    if ($vendor->isDot() || !$vendor->isDir() || in_array($vendorName, $vendorExclusions)) {
        continue;
    }

    $projects = new DirectoryIterator($vendor->getPathname());
    foreach ($projects as $project) {
        $projectName = $project->getFilename();
        if ($project->isDot() || !$project->isDir() || (isset($projectExclusions[$vendorName]) && in_array($projectName, $projectExclusions[$vendorName]))) {
            continue;
        }

        $componentNameParts[] = $projectName;
        $srcDirParts = getSrcDirParts($project->getPathname(), $vendorName, $projectName);
        if ($srcDirParts === false) {
            echo YELLOW . 'WARNING: ' . WHITE . 'could not determine source/library directory for ' . $vendorName
                . ' ' . $projectName . ', skipping' . PHP_EOL;
            continue;
        }
        $srcDir = $srcDirParts['srcDir'];
        if ($srcDir == '.') {
            $srcDir = '';
        }
        $srcDirPath = $project->getPathname() . '/' . $srcDir;

        if (isset($srcDirParts['namespaceDir'])) {
            $namespaceDir = $srcDirParts['namespaceDir'];
        } else {
            $namespaceDir = determineNamespaceDir($srcDirPath, $vendorName, $projectName);
            if ($namespaceDir === false) {
                echo YELLOW . 'WARNING: ' . WHITE . 'could not determine namespaced directory for ' . $vendorName . ' '
                    . $projectName . ', skipping' . PHP_EOL;
                continue;
            }
        }

        $componentNameParts = [$vendorName, $projectName];
        if ($srcDir != '') {
            $componentNameParts[] = trim($srcDir, '/');
        }
        $componentNameParts = array_merge($componentNameParts, explode('/', $namespaceDir));
        $componentName = implode('_', $componentNameParts);
        echo $componentName . '... ';
        $newComponents[$componentName] = true;

        if (in_array($componentName, $currentComponents)) {
            echo 'exists' . PHP_EOL;
            continue;
        }

        $compileSuccess = testCompile($componentName);
        if (!$compileSuccess) {
            $newComponents[$componentName] = false;
            echo YELLOW . 'failed compilation test' . WHITE . PHP_EOL;
            continue;
        }

        echo GREEN . 'added' . WHITE . PHP_EOL;
    }
}

saveComponents($newComponents);

echo 'DONE' . PHP_EOL;
exit(0);

function getSrcDirParts($path, $vendor, $project)
{
    $ucVendor = dashesToProperCase($vendor);
    $ucProject = dashesToProperCase($project);
    $srcDirOptions = ['', 'src/', 'library/', 'lib/'];
    if (file_exists($path . '/composer.json')) {
        $composerJson = json_decode(file_get_contents($path . '/composer.json'));
        if (isset($composerJson->autoload->{'psr-0'})) {
            foreach ($srcDirOptions as $srcDirOption) {
                if (in_array($srcDirOption, (array)$composerJson->autoload->{'psr-0'})) {
                    $ret = ['srcDir' => $srcDirOption];
                    $namespace = array_search($srcDirOption, (array)$composerJson->autoload->{'psr-0'});
                    if ($namespace != '') {
                        $ret['namespaceDir'] = trim(str_replace('\\', '/', $namespace), '/');
                    }
                    return $ret;
                }
            }

            $vendorProject = $ucVendor.'\\'.$ucProject;
            if (isset($composerJson->autoload->{'psr-0'}->$vendorProject)) {
                return [
                    'srcDir' => $composerJson->autoload->{'psr-0'}->$vendorProject,
                    'namespaceDir' => trim(str_replace('\\', '/', $vendorProject), '/')
                    ];
            } elseif (isset($composerJson->autoload->{'psr-0'}->$ucVendor)) {
                return [
                    'srcDir' => $composerJson->autoload->{'psr-0'}->$ucVendor,
                    'namespaceDir' => $ucVendor
                    ];
            } elseif (isset($composerJson->autoload->{'psr-0'}->$ucProject)) {
                return [
                    'srcDir' => $composerJson->autoload->{'psr-0'}->$ucProject,
                    'namespaceDir' => $ucProject
                    ];
            }
        }
    }

    // Haven't returned yet, try a few more options
    foreach ($srcDirOptions as $srcDirOption) {
        if ($srcDirOption == '') {
            continue;
        }
        if (is_dir($path . '/' . $srcDirOption)) {
            return ['srcDir' => $srcDirOption];
        }
    }
    $namespaceDir = determineNamespaceDir($path, $vendor, $project);
    if ($namespaceDir) {
        return [
            'srcDir' => '',
            'namespaceDir' => $namespaceDir
            ];
    }

    return false;
}

function determineNamespaceDir($path, $vendor, $project)
{
    $vendor = convertVendorToNamespace($vendor);
    $project = convertProjectToNamespace($vendor, $project);

    if (is_dir($path . '/' . $vendor . '/' . $project)) {
        return $vendor . '/' . $project;
    } else if (is_dir($path . '/' . $vendor)) {
        return $vendor;
    } else if (is_dir($path . '/' . $project)) {
        return $project;
    }

    return false;
}

function convertVendorToNamespace($vendor)
{
    $vendor = dashesToProperCase($vendor);
    $vendorConversions = [
        'Channelgrabber' => 'CG',
        'Zendframework' => 'Zend'
    ];
    if (isset($vendorConversions[$vendor])) {
        $vendor = $vendorConversions[$vendor];
    }

    return $vendor;
}

function convertProjectToNamespace($vendor, $project)
{
    $project = dashesToProperCase($project);

    $projectConversions = [];
    if (isset($projectConversions[$project])) {
        $project = $projectConversions[$project];
    }

    if ($vendor == 'Symfony') {
        $project = 'Component/'.$project;
    } elseif ($vendor == 'Zend') {
        $project = preg_replace('/^Zend/', '', $project);
    }

    return $project;
}

function saveComponents($components)
{
    ksort($components);
    $output = '<?php' . PHP_EOL
        . '/*' . PHP_EOL . ' * This file is generated but should still be committed' . PHP_EOL
        . ' */' . PHP_EOL
        . 'return array(' . PHP_EOL;
    foreach ($components as $component => $compiled) {
        if (!$compiled) {
            $output .= '//';
        }
        $output .= '    \'' . $component . '\',' . PHP_EOL;
    }
    $output .= ');';

    file_put_contents(COMPONENTS_FILE, $output);
}

function testCompile($component)
{
    exec('/usr/bin/php ' . getcwd() . '/bin/test_compile_di_component.php vendor ' . $component . ' > /dev/null 2>&1', $output, $ret);
    return ($ret === 0);
}

function dashesToProperCase($string)
{
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
}