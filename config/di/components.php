<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$controllerComponents = array(
    'CG'
);

$libraryComponents = array(
    'CG'
);

$vendorComponents = array_merge($frameworkComponents, array(

    "zendframework_zendframework_Zend",
    "channelgrabber_stdlib_CG_Stdlib",
    "channelgrabber_zf2-hal_CG_Zend_Hal",
    "phpspec_phpspec_PhpSpec",
    "phpspec_prophecy_Prophecy",
    "davedevelopment_phpmig_Phpmig",
    "codeception_codeception_Codeception",
    "nocarrier_hal_src_Nocarrier",
    "guzzle_guzzle_src_Guzzle",
    "channelgrabber_http_CG_Http",
    "channelgrabber_codeception_CG_Codeception",
    "channelgrabber_ui_CG_UI",
    "channelgrabber_zf2-stdlib_CG_Zend_Stdlib",
    "zendframework_zend-config_Zend_Config",
    "zendframework_zend-servicemanager_Zend_ServiceManager",
    "zendframework_zend-db_Zend_Db",
    "zendframework_zend-di_Zend_Di"

));

$components = array_merge($libraryComponents, $vendorComponents, $phpInternalComponents);
$componentTypes = [
    'controllers' => $controllerComponents,
    'library' => $libraryComponents,
    'vendor' => $vendorComponents
];