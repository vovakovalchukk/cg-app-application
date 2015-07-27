<?php
use CG\Di\Definition\CacheDefinition;
use CG\Di\DefinitionList;
use Zend\Cache\Storage\StorageInterface;
use Zend\Config\Config as ZendConfig;
use Zend\Db\Adapter\Adapter;
use Zend\Di\Config;
use Zend\Di\Di;
use Zend\Di\InstanceManager;
use Zend\Di\LocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;

return [
    'service_manager' => [
        'factories' => [
            Di::class => function(ServiceLocatorInterface $serviceManager) {
                /**
                 * @var $configuration array
                 */
                $configuration = $serviceManager->get('config');

                /**
                 * @var $cache StorageInterface
                 */
                $cache = $serviceManager->get('DiCacheStorage');

                $runtimeDefinition = new CacheDefinition(
                    null,
                    require dirname(dirname(__DIR__)) . '/bin/complete_classmap.php',
                    $cache
                );

                $definitionList = new DefinitionList([$runtimeDefinition->toArrayDefinition(), $runtimeDefinition]);
                $im = new InstanceManager();
                $config = new Config(isset($configuration['di']) ? $configuration['di'] : []);

                $di = new Di($definitionList, $im, $config);

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, Adapter::class);
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, Di::class);
                $im->addSharedInstance($serviceManager, ServiceManager::class);
                $im->addSharedInstance($di->get('config', array('array' => $configuration)), 'config');
                $im->addSharedInstance($di->get(ZendConfig::class, array('array' => $configuration)), 'app_config');
                $im->addSharedInstance((new PhpRenderer())->setResolver($serviceManager->get('viewresolver')), PhpRenderer::class);

                $eventManager = $serviceManager->get('EventManager');
                $im->addTypePreference(EventManagerInterface::class, get_class($eventManager));
                $im->addSharedInstance($eventManager, get_class($eventManager));

                return $di;
            },
        ],
        'shared' => [
            Di::class => true,
        ],
        'aliases' => [
            'Di' => Di::class,
        ],
    ],
    'di' => [
        'instance' => [
            'aliases' => [
                'Di' => Di::class,
                'config' => ZendConfig::class,
                'app_config' => ZendConfig::class,
            ],
            'preferences' => [
                LocatorInterface::class => Di::class,
            ],
        ],
    ],
];