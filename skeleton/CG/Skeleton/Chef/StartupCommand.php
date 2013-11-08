<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Chef\Role;
use CG\Skeleton\Chef\Node;
use CG\Skeleton\Chef\Environment\EnvironmentFactory;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const ROLES = 'roles/';
    const NODES = 'nodes/';
    const HOSTS = 'data_bags/hosts/';

    protected $defaults;

    public function __construct()
    {
        $this->defaults = array();
    }

    public function runCommands(Arguments $arguments, Config $config)
    {
        $this->saveRole($config);
        $this->saveNode($config);
        $this->setupHostname($config);
    }

    protected function saveRole(Config $config)
    {
        $roleName = $config->getRole();
        if (!$roleName) {
            $roleName = $config->getAppName();
            $config->setRole($roleName);
        }

        $roleFile = static::ROLES . $roleName . '.json';
        $role = new Role($roleFile);

        $role->addToRunList('role[web_app]');

        $role->save();

        exec(
            'git add ' . $roleFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated role ' . $roleName . '" --only -- ' . $roleFile
        );
    }

    protected function saveNode(Config $config)
    {
        $nodeFile = static::NODES . $config->getNode() . '.json';
        $node = new Node($nodeFile);

        $this->addRoleToNode($node, 'cg')
             ->addRoleToNode($node, 'database_storage')
             ->addRoleToNode($node, $config->getRole());

        $this->configureCapistranoOnNode($node, $config);
        $this->configureSiteOnNode($node, $config);

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node ' . $config->getNode() . '" --only -- ' . $nodeFile
        );
    }

    protected function setupHostname(Config $config)
    {
        $hostname = $config->getHostname();
        while (!$hostname) {
            $this->getConsole()->writeErrorStatus('Application hostname is not set');
            $hostname = $this->getConsole()->ask('What url will your app be available at');
        }
        $this->getConsole()->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $config->setHostname($hostname);
    }

    protected function setupIp(Config $config)
    {
        EnvironmentFactory::build($config->getEnvironment())->setupIp();
    }

    // Load Hosts object with current file data. Add new host. Save Hosts.
    protected function saveHost(Config $config)
    {
        $host = $config->getHost();

        $hostsFile = static::HOSTS . 'local' . '.json';
        $hosts = new Hosts($hostsFile);

        // Save to config
        $hosts->setHost('host', $config->getHostname(), '127.0.0.1');

        $hosts->save();

        exec(
            'git add ' . $hostsFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated role ' . $host . '" --only -- ' . $hostsFile
        );
    }

    public function getHosts()
    {
        return $this->hosts;
    }

    protected function addRoleToNode(Node $node, $role)
    {
        $node->addToRunList('role[' . $role . ']');
        return $this;
    }

    protected function configureCapistranoOnNode(Node $node, Config $config)
    {
        $node->setKey('cg|capistrano|' . $config->getAppName() . '|deploy_to', $config->getVmPath());
        $node->setKey('cg|capistrano|' . $config->getAppName() . '|shared_structure|config', 'config');
        $node->setKey('cg|capistrano|' . $config->getAppName() . '|shared_structure|config/autoload', 'config/autoload');
        $node->setKey('cg|capistrano|' . $config->getAppName() . '|symlinks|config/host.php', 'config/host.php');
    }

    protected function configureSiteOnNode(Node $node, Config $config)
    {
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|docroot', $config->getVmPath());
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|projectroot', '');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|webroot', 'public');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|configroot', 'config');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|dataroot', 'data');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|datadiroot', 'data/di');
        //$node->setKey('configure_sites|sites|' . $config->getAppName() . '|hostname', $config->getHostname()); // TODO remove me
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|enabled', true);
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|configautoloadroot', 'config/autoload');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|certificateroot', 'data/certificates');
    }
}