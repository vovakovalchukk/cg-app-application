<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define('PROJECT_ROOT', dirname(__DIR__));
chdir(PROJECT_ROOT);

