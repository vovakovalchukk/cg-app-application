<?php
return array (
  'modules' => 
  array (
    0 => 'Application',
    1 => 'NewRelic',
    2 => 'CG_Log',
    3 => 'CG_UI',
    4 => 'CG_Login',
    5 => 'CG_Register',
    6 => 'Mustache',
    7 => 'CG_SSO',
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