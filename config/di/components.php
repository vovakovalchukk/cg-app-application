<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$moduleComponents = array(
    'Application_src_Application_Controller'
);

$vendorComponents = array_merge($frameworkComponents, array(
        "channelgrabber_stdlib_CG_Stdlib",
        "channelgrabber_zf2-hal_CG_Zend_Hal",
        "phpspec_prophecy_src_Prophecy",
        "codeception_codeception_src_Codeception",
        "nocarrier_hal_src_Nocarrier",
        "guzzle_guzzle_src_Guzzle",
        "channelgrabber_http_CG_Http",
        "channelgrabber_codeception_CG_Codeception",
        "channelgrabber_ui_CG_UI",
        "channelgrabber_zf2-stdlib_CG_Zend_Stdlib",
        "zendframework_zendframework_library_Zend_Config",
        "zendframework_zendframework_library_Zend_ServiceManager",
        "zendframework_zendframework_library_Zend_Db",
        "zendframework_zendframework_library_Zend_Di"
));

$components = array_merge($libraryComponents, $vendorComponents, $phpInternalComponents);
$componentTypes = [
    'module' => $moduleComponents,
    'vendor' => $vendorComponents
];