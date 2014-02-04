<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;
use CG\Skeleton\Chef\StartupCommand as Chef;
use CG\Skeleton\Chef\Role;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\Node;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Chef\Hosts;

class Dual extends Environment {

    protected $name = 'Dual';
    protected $suffix = '.local';

    protected $environmentNodes = array('infrastructure', 'services', 'frontend');

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
        $nodeData = $this->getNodeData();
        $node = $this->getEnvironmentConfig()->getNode();

        while (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (isset($node)) {
                $ipAddress = $nodeData->getNode($node)->getVmIp();
                break;
            }
            $console->writeErrorStatus('IP address is not set or is invalid');
            $chosenNode = $console->askWithOptions(
                'Which node would you like to run your app on?',
                $this->environmentNodes,
                $this->environmentNodes[1]
            );
            $ipAddress = $nodeData->getNode($chosenNode)->getVmIp();
        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function setupHostsFile(Startup $console)
    {
        $console->writeStatus(
            'Saving ip addresses to /etc/hosts '
            . Startup::COLOR_PURPLE . '(You may be prompted for your password)' . Startup::COLOR_RESET
        );

        foreach($this->getHosts() as $host) {
            $this->updateHostsFileEntry($host['ip'], $host['hostname']);
        }

        $console->writeStatus('IP addresses saved to /etc/hosts');
    }

    public function vagrantUp(Console $console)
    {
        foreach($this->environmentNodes as $node) {
            passthru('vagrant up ' . $node);
        }
    }

    public function vagrantProvision(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to provision',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant provision ' . $nodeChoice);
    }

    public function vagrantSsh(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to connect to',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant ssh ' . $nodeChoice);
    }

    public function vagrantReload(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to restart',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant reload ' . $nodeChoice);
    }

    public function vagrantHalt(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to stop',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant halt ' . $nodeChoice);
    }

    public function getInitialNodeRunList()
    {
        return array('cg');
    }

    public function setupNode(Startup $console)
    {
        $node = $this->getEnvironmentConfig()->getNode();

        while (!$node) {
            $console->writeErrorStatus('No node set');
            $node = strtolower($console->askWithOptions(
                'What node will this application go on',
                $this->environmentNodes,
                $this->environmentNodes[1]
            ));
        }
        $console->writeStatus('Application configured for node \'' . $node . '\'');
        $this->getEnvironmentConfig()->setNode($node);

        $this->setupEnvironmentRole();
    }

    protected function setupEnvironmentRole()
    {
        $cwd = getcwd();
        chdir($this->getConfig()->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $this->getConfig()->getBranch() . ' 2>&1;');

        $chosenEnvironmentNode = $this->getEnvironmentConfig()->getNode();
        $roleFile = Chef::ROLES . $chosenEnvironmentNode . '.json';
        $role = new Role($roleFile);

        $role->addToRunList('role[' . $this->getConfig()->getAppName() . ']');
        $role->save();

        chdir($cwd);
    }

    public function setDatabaseStorageKey(SkeletonConfig $config, BaseConfig $moduleConfig, Node &$node)
    {
        $infrastructureNodeFile = Chef::NODES . 'infrastructure.json';
        $infrastructureNode = new Node($infrastructureNodeFile);

        $databaseStorageKey = 'database_storage|';
        if ($moduleConfig->isEnabled()) {
            $infrastructureNode->setKey($databaseStorageKey . 'enabled', true);
            $infrastructureNode->setKey($databaseStorageKey . 'storage_choice', $moduleConfig->getStorageNode());
        } else {
            $infrastructureNode->removeKey($databaseStorageKey);
        }

        $infrastructureNode->save();
    }

}
