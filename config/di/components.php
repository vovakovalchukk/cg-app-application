<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$libraryComponents = array();

$vendorComponents = array_merge($frameworkComponents, array(
    'nocarrier_hal_src_Nocarrier',
    'guzzle_guzzle_src_Guzzle',
    'channelgrabber_stdlib_CG_Stdlib',
    'channelgrabber_sso_CG_Sso',
    'channelgrabber_guzzle_CG_Guzzle'
));

$components = array_merge($libraryComponents, $vendorComponents, $phpInternalComponents);