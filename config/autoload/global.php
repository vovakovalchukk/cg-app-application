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

use Zend\Config\Config;
use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager;
use Zend\ServiceManager\ServiceManager;

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Di\Di' => function($serviceManager) {
                $configuration = $serviceManager->get('config');

                $definition = new CG\Di\Definition\RuntimeDefinition(
                    null,
                    include 'bin/complete_classmap.php'
                );
                $definitionList = new Zend\Di\DefinitionList([$definition]);
                $im = new Zend\Di\InstanceManager();
                $di = new Zend\Di\Di($definitionList, $im, new Zend\Di\Config(
                    isset($configuration['di']) ? $configuration['di'] : array()
                ));

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, 'Zend\Db\Adapter\Adapter');
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, 'Zend\Di\Di');
                $im->addSharedInstance($di->get('config', array('array' => $configuration)), 'config');
                $im->addSharedInstance($di->get(Config::class, array('array' => $configuration)), 'app_config');
                $im->addSharedInstance($serviceManager, ServiceManager::class);

                return $di;
            }
        ),
        'shared' => array(
            'Zend\Di\Di' => true
        ),
        'aliases' => array(
            'Di' => 'Zend\Di\Di'
        )
    ),
    'di' => array(
        'instance' => array(
            'aliases' => array(
                'Di' => 'Zend\Di\Di',
                'config' => Config::class,
                'app_config' => Config::class
            ),
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                EventManagerInterface::Class => EventManager::Class,
            )
        )
    )
);
