<?php
namespace CG\Skeleton\Module\Redis;

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
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Module extends AbstractModule implements EnableInterface, ConfigureInterface, DisableInterface
{
    use \CG\Skeleton\GitTicketIdTrait;

    public function getModuleName()
    {
        return 'Redis';
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

        $composerRequires = array(
            'predis/predis:~0.8.3',
            'channelgrabber/predis:~1.0.1'
        );

        if ($moduleConfig->isEnabled()) {
            $this->getComposer()->addRequires($moduleConfig, $composerRequires);
        } else {
            $this->getComposer()->removeRequires($moduleConfig, $composerRequires, false);
        }
    }

    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->configureModule($arguments, $config, $moduleConfig, true);
    }

    public function configureModule(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $this->configureRedisAdapters($arguments, $config, $moduleConfig, $reconfigure);
    }

    public function configureRedisAdapters(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        if (!$moduleConfig->isEnabled()) {
            return;
        }
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');

        ob_start();
        passthru('knife solo data bag show redis local -F json 2>/dev/null');
        $redisInstancesJson = json_decode(ob_get_clean(), true) ?: array();

        $redisInstances = array();
        if (isset($redisInstancesJson['instance'])) {
            foreach (array_keys($redisInstancesJson['instance']) as $redisInstance) {
                $redisInstances[$redisInstance] = $redisInstance;
            }
        }
        if (empty($redisInstances)) {
            $moduleConfig->setEnabled(false);
            $this->getConsole()->writelnErr(
                'No Redis Instances configured in redis data bag for local environment  - ' . $this->getModuleName() . ' Disabled'
            );
            chdir($cwd);
            return;
        }

        $configuredAdapters = array();
        while ($reconfigure || empty($configuredAdapters)) {
            $this->getConsole()->writeln('Available Redis Adapters:');
            foreach ($redisInstances as $instance) {
                $this->getConsole()->writeln('   * ' . $instance);
            }

            $adaptersString = $this->getConsole()->ask('Please choose the adapters you require (separated by spaces)');

            foreach (explode(" ", $adaptersString) as $adapter) {
                $configuredAdapters[$adapter] = true;
            }

            $reconfigure = false;
        };

        $moduleConfig->setRedisAdapters($configuredAdapters);
        chdir($cwd);
    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig, Environment $environment)
    {
        $nodeFile = Chef::NODES . $environment->getEnvironmentConfig()->getNode() . '.json';
        $node = new Node($nodeFile);

        $configKey = 'configure_sites|sites|' . $config->getAppName() . '|redis_client|';

        if ($moduleConfig->isEnabled()) {
            $node->setKey($configKey . 'enabled', true);

            $configuredAdapters = $moduleConfig->getRedisAdapters();
            $adapters = array();
            if (empty($configuredAdapters)) {
                $adapters[] = "unreliable";
            } else {
                foreach ($configuredAdapters as $adapter => $enabled) {
                    if ($enabled) {
                        $adapters[] = $adapter;
                    }
                }
            }

            $node->setKey($configKey . 'adapters', $adapters, false);
            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.redis.global.php',
                'config/autoload/di.redis.global.php'
            );
        } else {
            $node->removeKey('configure_sites|sites|' . $config->getAppName() . '|redis_client');
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.redis.global.php');
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
