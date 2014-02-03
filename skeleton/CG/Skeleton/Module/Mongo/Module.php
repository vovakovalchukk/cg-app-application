<?php
namespace CG\Skeleton\Module\Mongo;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\DisableInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Node;

class Module extends AbstractModule implements EnableInterface, ConfigureInterface, DisableInterface
{
    use \CG\Skeleton\GitTicketIdTrait;

    public function getModuleName()
    {
        return 'Mongo';
    }

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }

    public function applyConfiguration(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig, Environment $environment)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig, $environment);
        chdir($cwd);
    }

    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->configureModule($arguments, $config, $moduleConfig, true);
    }

    public function configureModule(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $this->configureMongoAdapters($arguments, $config, $moduleConfig, $reconfigure);
    }

    public function configureMongoAdapters(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        if (!$moduleConfig->isEnabled()) {
            return;
        }
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');

        ob_start();
        passthru('knife solo data bag show mongo local -F json 2>/dev/null');
        $mongoInstancesJson = json_decode(ob_get_clean(), true) ?: array();

        $mongoInstances = array();
        if (isset($mongoInstancesJson['instance'])) {
            foreach (array_keys($mongoInstancesJson['instance']) as $mongoInstance) {
                $mongoInstances[$mongoInstance] = $mongoInstance;
            }
        }

        if (empty($mongoInstances)) {
            $moduleConfig->setEnabled(false);
            $this->getConsole()->writelnErr(
                'No Mongo Instances configured in mongo data bag for local environment  - ' . $this->getModuleName() . ' Disabled'
            );
            chdir($cwd);
            return;
        }

        $configuredAdapters = array();
        while ($reconfigure || empty($configuredAdapters)) {
            $this->getConsole()->writeln('Available Mongo Adapters:');
            foreach ($mongoInstances as $instance) {
                $this->getConsole()->writeln('   * ' . $instance);
            }

            $adaptersString = $this->getConsole()->ask('Please choose the adapters you require (separated by spaces)');

            foreach (explode(" ", $adaptersString) as $adapter) {
                $configuredAdapters[$adapter] = true;
            }

            $reconfigure = false;
        };

        $moduleConfig->setMongoAdapters($configuredAdapters);
        chdir($cwd);
    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig, Environment $environment)
    {
        $nodeFile = Chef::NODES . $environment->getEnvironmentConfig()->getNode() . '.json';
        $node = new Node($nodeFile);

        $configKey = 'configure_sites|sites|' . $config->getAppName() . '|mongo_client|';

        if ($moduleConfig->isEnabled()) {
            $node->setKey($configKey . 'enabled', true);

            $configuredAdapters = $moduleConfig->getMongoAdapters();
            $adapters = array();
            if (empty($configuredAdapters)) {
                $adapters[] = "mongodb";
            } else {
                foreach ($configuredAdapters as $adapter => $enabled) {
                    if ($enabled) {
                        $adapters[] = $adapter;
                    }
                }
            }

            $node->setKey($configKey . 'adapters', $adapters, false);

            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.mongo.global.php',
                'config/autoload/di.mongo.global.php'
            );
        } else {
            $node->removeKey('configure_sites|sites|' . $config->getAppName() . '|mongo_client');
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.mongo.global.php');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node ' . $environment->getEnvironmentConfig()->getNode()
            . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }

    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(false);
    }
}