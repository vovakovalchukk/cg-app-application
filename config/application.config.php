<?php
return array (
  'modules' => 
  array (
    0 => 'Application',
    1 => 'NewRelic',
    2 => 'CG_Log',
    3 => 'Mustache',
    4 => 'CG_UI',
    5 => 'CG_Permission',
    6 => 'CG_Login',
    7 => 'CG_Register',
    8 => 'Orders',
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
