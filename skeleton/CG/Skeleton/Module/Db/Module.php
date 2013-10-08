<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Module\DisableInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Node;
use DirectoryIterator;

class Module extends AbstractModule implements EnableInterface, ConfigureInterface, DisableInterface
{
    use \CG\Skeleton\ComposerTrait;

    public function getModuleName()
    {
        return 'Db';
    }

    public function getConfigClass()
    {
        return 'CG\Skeleton\Module\BaseConfig';
    }

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }

    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->configureModule($arguments, $config, $moduleConfig, true);
    }

    public function configureModule(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        $this->configureStorageNode($arguments, $config, $moduleConfig, $reconfigure);
        $this->configureDatabaseName($arguments, $config, $moduleConfig, $reconfigure);
        $this->configureDatabaseAdapters($arguments, $config, $moduleConfig, $reconfigure);
    }

    public function configureStorageNode(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        if (!$moduleConfig->isEnabled()) {
            return;
        }

        $storageNodes = array();
        for (
            $iterator = new DirectoryIterator($config->getInfrastructurePath() . '/tools/chef/data_bags/');
            $iterator->valid();
            $iterator->next()
        ) {
            $match = array();
            if ($iterator->isDir() && preg_match('/^storage_(?<node>.+)_users$/', $iterator->getBasename(), $match)) {
                $storageNodes[$match['node']] = $match['node'];
            }
        }

        if (empty($storageNodes)) {
            $moduleConfig->setEnabled(false);
            $this->getConsole()->writelnErr('No Storage Nodes Available - ' . $this->getModuleName() . ' Disabled');
            return;
        }

        $storageNode = $moduleConfig->getStorageNode();

        while ($reconfigure || !$storageNode || !isset($storageNodes[$storageNode])) {
            $reconfigure = false;

            $this->getConsole()->writeln('Available Storage Nodes:');
            foreach ($storageNodes as $node) {
                $this->getConsole()->writeln('   * ' . $node);
            }

            $storageNode = $this->getConsole()->ask(
                'Please specify the storage node you wish to connect to',
                $storageNode ?: reset($storageNodes)
            );
        }

        $moduleConfig->setStorageNode($storageNode);
    }

    public function configureDatabaseName(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        if (!$moduleConfig->isEnabled()) {
            return;
        }

        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');

        ob_start();
        passthru('knife solo data bag show storage_' . $moduleConfig->getStorageNode() . ' local -F json 2>/dev/null');
        $databasesJson = json_decode(ob_get_clean(), true) ?: array();

        $databases = array();
        if (isset($databasesJson['db'])) {
            foreach (array_keys($databasesJson['db']) as $database) {
                $databases[$database] = $database;
            }
        }

        if (empty($databases)) {
            $moduleConfig->setEnabled(false);
            $this->getConsole()->writelnErr(
                'No Databases Available for \'' . $moduleConfig->getStorageNode() . '\' - ' . $this->getModuleName() . ' Disabled'
            );
            chdir($cwd);
            return;
        }

        $databaseName = $moduleConfig->getDatabaseName();
        while ($reconfigure || !$databaseName || !isset($databases[$databaseName])) {
            $reconfigure = false;

            $this->getConsole()->writeln('Available Databases:');
            foreach ($databases as $database) {
                $this->getConsole()->writeln('   * ' . $database);
            }

            $databaseName = $this->getConsole()->ask(
                'Please specify the database you wish to connect to',
                $databaseName ?: reset($databases)
            );
        }
        $moduleConfig->setDatabaseName($databaseName);

        chdir($cwd);
    }

    public function configureDatabaseAdapters(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, $reconfigure = false)
    {
        if (!$moduleConfig->isEnabled()) {
            return;
        }
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');

        ob_start();
        passthru('knife solo data bag show storage_' . $moduleConfig->getStorageNode() . '_users local -F json 2>/dev/null');
        $databaseUsersJson = json_decode(ob_get_clean(), true) ?: array();

        $availableUsers = array();
        if (isset($databaseUsersJson['users'])) {
            foreach (array_keys($databaseUsersJson['users']) as $databaseUser) {
                $availableUsers[$databaseUser] = $databaseUser;
            }
        }

        if (empty($availableUsers)) {
            $moduleConfig->setEnabled(false);
            $this->getConsole()->writelnErr(
                'No Database Users Available for \'' . $moduleConfig->getDatabaseName() . '\' - ' . $this->getModuleName() . ' Disabled'
            );
            chdir($cwd);
            return;
        }

        $configuredAdapters = $moduleConfig->getDatabaseAdapters();
        while (true) {
            if (!$reconfigure && !empty($configuredAdapters)) {
                break;
            }

            $this->getConsole()->writeln('Available Database Users:');
            foreach ($availableUsers as $user) {
                $this->getConsole()->writeln('   * ' . $user);
            }


            $availableAdapters = array('readAdapter', 'writeAdapter', 'fastReadAdapter');
            foreach ($availableAdapters as $adapter) {
                $user = $this->getConsole()->ask(
                    'Please choose the user you wish to use for adapter "'. $adapter .'"');
                $configuredAdapters[$adapter] = array('user' => $user);
            }

            $reconfigure = false;
        };

        $moduleConfig->setDatabaseAdapters($configuredAdapters);
        chdir($cwd);
    }

    public function applyConfiguration(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $this->validateConfig($moduleConfig);
        $this->configureModule($arguments, $config, $moduleConfig);

        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->updateNode($arguments, $config, $moduleConfig);
        chdir($cwd);

        $this->updateComposer($moduleConfig, array(
            'robmorgan/phinx:*'
        ));
    }

    protected function updateNode(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $nodeFile = Chef::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        if ($moduleConfig->isEnabled()) {
            $node->setKey('database|enabled', true);
            $node->setKey('database|storage_choice', $moduleConfig->getStorageNode());
            $node->setKey('database|database_choice', $moduleConfig->getDatabaseName());

            $node->removeKey('database|adapters');
            foreach ($moduleConfig->getDatabaseAdapters() as $adapter => $user) {
                $node->setKey('database|adapters|' . $adapter, $user);
            }

            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/database.local.php',
                'config/autoload/database.local.php'
            );
            $node->setKey('configure_sites|sites|' . $config->getAppName() . '|phinxroot', 'phinx');
            $node->setKey(
                'cg|capistrano|' . $config->getAppName() . '|symlinks|phinx/phinx.yml', 'phinx/phinx.yml'
            );
        } else {
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/autoload/database.local.php');
            $node->removeKey('configure_sites|sites|' . $config->getAppName() . '|phinxroot');
            $node->removeKey('cg|capistrano|' . $config->getAppName() . '|symlinks|phinx/phinx.yml');
            $node->removeKey('database');
        }

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "SKELETON: Updated node ' . $config->getNode() . ' with \'' . $this->getName() . '\' config" --only -- ' . $nodeFile
        );
    }

    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(false);
    }
}