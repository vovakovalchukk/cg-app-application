<?php
return array (
  'modules' => 
  array (
    0 => 'Application',
    1 => 'NewRelic',
    2 => 'CG_Log',
    3 => 'Mustache',
    4 => 'CG_Register',
    5 => 'Orders',
    6 => 'CG_UI',
    7 => 'CG_Login',
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