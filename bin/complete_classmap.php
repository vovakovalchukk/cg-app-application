<?php
$composerClassmap = require 'vendor/composer/autoload_classmap.php';
$modulesClassmap = require 'config/di/modules_classmap.php';

return array_merge($composerClassmap, $modulesClassmap);