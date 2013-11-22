<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Chef\Role;
use CG\Skeleton\Chef\Node;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const ROLES = 'roles/';
    const NODES = 'nodes/';

    protected $defaults;

    public function __construct()
    {
        $this->defaults = array();
    }

    public function runCommands(Arguments $arguments, Config $config)
    {
        $this->saveRole($config);
        $this->saveNode($config);
    }

    protected function saveRole(Config $config)
    {
        $roleName = $config->getRole();
        if (!$roleName) {
            $roleName = str_replace(" ", "-", $config->getAppName());
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

    protected function addRoleToNode(Node $node, $role)
    {
        $node->addToRunList('role[' . $role . ']');
        return $this;
    }

    protected function configureCapistranoOnNode(Node $node, Config $config)
    {
        $siteName = str_replace(" ", "-", $config->getAppName());

        $node->setKey('cg|capistrano|' . $siteName . '|deploy_to', $config->getVmPath());
        $node->setKey('cg|capistrano|' . $siteName . '|shared_structure|config', 'config');
        $node->setKey('cg|capistrano|' . $siteName . '|shared_structure|config/autoload', 'config/autoload');
        $node->setKey('cg|capistrano|' . $siteName . '|symlinks|config/host.php', 'config/host.php');
    }

    protected function configureSiteOnNode(Node $node, Config $config)
    {
        $siteName = str_replace(" ", "-", $config->getAppName());

        $node->setKey('configure_sites|sites|' . $siteName . '|docroot', $config->getVmPath());
        $node->setKey('configure_sites|sites|' . $siteName . '|projectroot', '');
        $node->setKey('configure_sites|sites|' . $siteName . '|webroot', 'public');
        $node->setKey('configure_sites|sites|' . $siteName . '|configroot', 'config');
        $node->setKey('configure_sites|sites|' . $siteName . '|dataroot', 'data');
        $node->setKey('configure_sites|sites|' . $siteName . '|datadiroot', 'data/di');
        $node->setKey('configure_sites|sites|' . $siteName . '|hostname', $config->getHostname());
        $node->setKey('configure_sites|sites|' . $siteName . '|enabled', true);
        $node->setKey('configure_sites|sites|' . $siteName . '|configautoloadroot', 'config/autoload');
        $node->setKey('configure_sites|sites|' . $siteName . '|certificateroot', 'data/certificates');
        $node->setKey('configure_sites|sites|' . $siteName . '|configmoduleroot', 'config/module');
        $node->setKey('configure_sites|sites|' . $siteName . '|modules', array(
                                                           'module config goes here' => 'do not delete this placeholder'
        ));
        $node->setKey('configure_sites|sites|' . $siteName . '|application_config', array(
                                                                            'application_name' => $config->getAppName()
        ));
  }
}