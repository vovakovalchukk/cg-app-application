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

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Di\Di' => function($serviceManager) {
                $configuration = $serviceManager->get('Config');
                return new Zend\Di\Di(null, null, new Zend\Di\Config(
                    isset($configuration['di']) ? $configuration['di'] : array()
                ));
            }
        ),
        'shared' => array(
            'Zend\Di\Di' => true
        )
    ),
    'di' => array(
        'instance' => array(
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di'
            )
        )
    )
);
