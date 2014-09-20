<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager;

return array(
    'di' => array(
        'instance' => array(
            'preferences' => array(
                EventManagerInterface::Class => EventManager::Class,
            )
        )
    )
);
