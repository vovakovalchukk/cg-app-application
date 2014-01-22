<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\Node;
use CG\Skeleton\Config as SkeletonConfig;

class Local extends Environment {

    protected $name = 'Local';
    protected $suffix = '.local';

    public function getName()
    {
        return $this->name;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setupIp(Startup $console)
    {
        $ipAddress = $this->getEnvironmentConfig()->getIp();
        $node = $this->getEnvironmentConfig()->getNode();

        $configuredHosts = $this->getHosts();

        while (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $console->writeErrorStatus('IP address is not set or is invalid');

            if (!empty($configuredHosts)) {
                $console->writeln('The following ips are in use');
                foreach ($configuredHosts as $host) {
                    $console->writeln('   * ' . $host['ip'] . ' => ' . $host['hostname']);
                }
                $console->writeln('Please remember other IPs may be in use, please confer with other developers before setting an ip address');
            }

            $ipAddress = $console->ask('What IP address would you like to access the vm for this node', '192.168.33.21');
        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
        $this->getNodeData()->getNode($node)->setVmIp($ipAddress);

        $cwd = getcwd();
        chdir($this->getConfig()->getInfrastructurePath() . '/tools/vagrant');
        $this->getNodeData()->save();
        chdir($cwd);
    }

    public function setupHostsFile(Startup $console)
    {
        $console->writeStatus(
            'Saving ip to /etc/hosts '
            . Startup::COLOR_PURPLE . '(You may be prompted for your password)' . Startup::COLOR_RESET
        );

        $this->updateHostsFileEntry(
            $this->getEnvironmentConfig()->getIp(),
            $this->getEnvironmentConfig()->getHostname($this->getConfig(), $this)
        );

        $console->writeStatus('IP saved to /etc/hosts');
    }

    public function vagrantUp(Console $console)
    {
        passthru('vagrant up ' . $this->getEnvironmentConfig()->getNode());
    }

    public function vagrantProvision(Console $console)
    {
        passthru('vagrant provision ' . $this->getEnvironmentConfig()->getNode());
    }

    public function vagrantSsh(Console $console)
    {
        passthru('vagrant ssh ' . $this->getEnvironmentConfig()->getNode());
    }

    public function vagrantReload(Console $console)
    {
        passthru('vagrant reload ' . $this->getEnvironmentConfig()->getNode());
    }

    public function vagrantHalt(Console $console)
    {
        passthru('vagrant halt ' . $this->getEnvironmentConfig()->getNode());
    }

    public function setupNode(Startup $console)
    {
        $node = $this->getEnvironmentConfig()->getNode();
        while (!$node) {
            $console->writeErrorStatus('No node set');
            $node = $console->ask('What node will this application go on');
        }
        $console->writeStatus('Application configured for node \'' . $node . '\'');
        $this->getEnvironmentConfig()->setNode($node);
    }

    public function getInitialNodeRunList()
    {
        return array('cg', 'database_storage', $this->getConfig()->getRole());
    }

    public function setDatabaseStorageKey(SkeletonConfig $config, BaseConfig $moduleConfig, Node &$node)
    {
        $databaseStorageKey = 'database_storage|';
        if ($moduleConfig->isEnabled()) {
            $node->setKey($databaseStorageKey . 'enabled', true);
            $node->setKey($databaseStorageKey . 'storage_choice', $moduleConfig->getStorageNode());
        } else {
            $node->removeKey($databaseStorageKey);
        }
    }
}
