<?php
namespace CG\Skeleton\Module\GearmanWorker;

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
    use \CG\Skeleton\ComposerTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    public function getModuleName()
    {
        return 'GearmanWorker';
    }

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }

    public function applyConfiguration(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig);
        chdir($cwd);
    }

    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $nodeFile = Chef::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        $configKey = 'configure_sites|sites|' . $config->getAppName() . '|gearman_worker|';

        if ($moduleConfig->isEnabled()) {
            $node->setKey($configKey . 'enabled', true);
            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.gearman_worker.global.php',
                'config/autoload/di.gearman_worker.global.php'
            );
        } else {
            $node->removeKey('configure_sites|sites|' . $config->getAppName() . '|gearman_worker');
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/di.gearman_worker.global.php');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node ' . $config->getNode() . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }

    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(false);
    }
}