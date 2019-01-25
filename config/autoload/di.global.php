<?php
use CG\Di\Definition\CacheDefinition;
use CG\Di\DefinitionList;
use CG\Di\Di;
use Zend\Config\Config as ZendConfig;
use Zend\Db\Adapter\Adapter;
use Zend\Di\Config;
use Zend\Di\Di as ZendDi;
use Zend\Di\InstanceManager;
use Zend\Di\LocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;
use CG_Billing\Package\Service as BillingPackageService;

return [
    'service_manager' => [
        'factories' => [
            Di::class => function(ServiceLocatorInterface $serviceManager) {
                /** @var $configuration array */
                $configuration = $serviceManager->get('config');

                $runtimeDefinition = new CacheDefinition(
                    $introspectionStrategy = null,
                    $explicitClasses = (require dirname(dirname(__DIR__)) . '/bin/complete_classmap.php'),
                    $cachePrefix = (ENVIRONMENT !== 'dev' ? $configuration['application_name'] ?? null : null)
                );

                $definitionList = new DefinitionList([$runtimeDefinition]);
                $im = new InstanceManager();
                $config = new Config($configuration['di'] ?? []);

                $di = new Di($definitionList, $im, $config);

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, Adapter::class);
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, Di::class);
                $im->addSharedInstance($di, ZendDi::class);
                $im->addSharedInstance($serviceManager, ServiceManager::class);
                $im->addSharedInstance($di->get('config', array('array' => $configuration)), 'config');
                $im->addSharedInstance($di->get(ZendConfig::class, array('array' => $configuration)), 'app_config');
                $im->addSharedInstance((new PhpRenderer())->setResolver($serviceManager->get('viewresolver')), PhpRenderer::class);

                $eventManager = $serviceManager->get('EventManager');
                $im->addTypePreference(EventManagerInterface::class, get_class($eventManager));
                $im->addSharedInstance($eventManager, get_class($eventManager));

                return $di;
            },
            ZendDi::class => function(ServiceLocatorInterface $serviceManager) {
                return $serviceManager->get(Di::class);
            },
            // Hack to prevent ServiceManager treating two classes as one because \ = _ in namespace!
            BillingPackageService::class => function(ServiceLocatorInterface $serviceManager, $alias, $class) {
                return $serviceManager->get(Di::class)->get($class);
            }
        ],
        'shared' => [
            Di::class => true,
            ZendDi::class => true,
            BillingPackageService::class => false,
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
                ZendDi::class => Di::class,
                LocatorInterface::class => Di::class,
            ],
        ],
    ],
];