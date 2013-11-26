<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Chef\Role;
use CG\Skeleton\Chef\Node;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Chef\Hosts;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const ROLES = 'roles/';
    const NODES = 'nodes/';
    const HOSTS = 'data_bags/hosts/';

    protected $defaults;
    protected $console;

    public function __construct(Startup $console)
    {
        $this->setConsole($console);
        $this->defaults = array();
    }

    public function setConsole(Startup $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function runCommands(Arguments $arguments, Config $config, Environment $environment)
    {
        $this->saveRole($config);
        $this->saveNode($config, $environment);
        $this->setupIp($config, $environment);
        $this->setupHostname($config, $environment);
        $this->saveHosts($config, $environment);
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

    protected function saveNode(Config $config, Environment $environment)
    {
        $nodeFile = static::NODES . $environment->getEnvironmentConfig()->getNode() . '.json';
        $node = new Node($nodeFile);

        foreach ($environment->getInitialNodeRunList() as $role) {
            $this->addRoleToNode($node, $role);
        }

        $this->configureCapistranoOnNode($node, $config);
        $this->configureSiteOnNode($node, $config);

        $node->save();

        exec(
            'git add ' . $nodeFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node ' . $environment->getEnvironmentConfig()->getNode() . '" --only -- ' . $nodeFile
        );
    }

    protected function saveHosts(Config $config, Environment $environment)
    {
        $hostsFile = static::HOSTS . strtolower($environment->getName()) . '.json';
        $hosts = new Hosts($hostsFile, $environment->getName());

        $hosts->setHost(
            $config->getAppName(),
            $environment->getEnvironmentConfig()->getHostname($config, $environment),
            $environment->getEnvironmentConfig()->getIp()
        );

        $hosts->save();

        exec(
            'git add ' . $hostsFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated hosts with ' . $config->getAppName() . '" --only -- ' . $hostsFile
        );
    }

    protected function setupHostname(Config $config, Environment $environment)
    {
        $hostname = $environment->getEnvironmentConfig()->getHostname($config, $environment);
        while (!$hostname) {
            $this->getConsole()->writeErrorStatus('Application hostname is not set');
            $hostname = $this->getConsole()->ask('What url will your app be available at');
        }
        $this->getConsole()->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $environment->getEnvironmentConfig()->setHostname($hostname);
    }

    protected function setupIp(Config $config, Environment $environment)
    {
        $environment->setupIp($this->getConsole());

        $this->getConsole()->writeStatus(
            'Saving ip to /etc/hosts '
            . Startup::COLOR_PURPLE . '(You may be prompted for your password)' . Startup::COLOR_RESET
        );

        $environmentConfig = $environment->getEnvironmentConfig();
        $hostname = $environmentConfig->getHostname($config, $environment);
        exec(
            'grep -q -e "' . $environmentConfig->getIp() . ' ' . $hostname . '" /etc/hosts'
            . ' || echo "' . $environmentConfig->getIp() . ' ' . $hostname . '" | sudo tee -a /etc/hosts'
        );
        $this->getConsole()->writeStatus('IP saved to /etc/hosts');
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
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|enabled', true);
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|configautoloadroot', 'config/autoload');
        $node->setKey('configure_sites|sites|' . $config->getAppName() . '|certificateroot', 'data/certificates');
    }
}