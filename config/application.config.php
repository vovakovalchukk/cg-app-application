<?php
return array (
  'modules' => 
  array (
    0 => 'Application',
    1 => 'NewRelic',
    2 => 'CG_Log',
    3 => 'CG_Mustache',
    4 => 'CG_UI',
    5 => 'CG_Permission',
    6 => 'CG_Login',
    7 => 'CG_Register',
    8 => 'CG_SSO',
    9 => 'Orders',
    10 => 'Settings',
    11 => 'CG_Email_Template',
    12 => 'CG_Amazon',
    13 => 'CG_Ebay',
    14 => 'CG_Channel',
    15 => 'CG_Sessions',
    16 => 'CG_Usage',
    17 => 'CG_RoyalMail',
  ),
  'module_listener_options' => 
  array (
    'module_paths' => 
    array (
      0 => './module',
      1 => './vendor',
    ),
    'config_glob_paths' => 
    array (
      0 => 'config/autoload/{,*.}{global,local}.php',
    ),
  ),
);