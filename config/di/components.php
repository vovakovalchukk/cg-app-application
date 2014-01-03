<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$moduleComponents = array(
    'Application_src_Application_Controller'
);

$vendorComponents = array_merge($frameworkComponents, require 'vendor_components.php');

$componentTypes = [
    'module' => $moduleComponents,
    'vendor' => $vendorComponents
];