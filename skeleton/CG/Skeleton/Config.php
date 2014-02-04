<?php
namespace CG\Skeleton;

use Zend\Config\Config as ZendConfig;

class Config extends ZendConfig
{
    const PROJECT_BASE_PATH = 'PROJECT_BASE_PATH';
    const INFRASTRUCTURE_PATH = 'INFRASTRUCTURE_PATH';
    const INFRASTRUCTURE_NAME = 'CGInfrastructure-V4';
    const INFRASTRUCTURE_REPOSITORY = 'git@bitbucket.org:channelgrabber/cginfrastructure-v4.git';
    const INFRASTRUCTURE_BRANCH = 'origin/master';
    const BRANCH = 'BRANCH';
    const NODE = 'NODE';
    const APP_NAME = 'APP_NAME';
    const VM_PATH = 'VM_PATH';
    const ROLE = 'ROLE';
    const ENVIRONMENT = 'ENVIRONMENT';

    protected $classMap = array(
        'Vagrant' => 'CG\Skeleton\Vagrant\Config',
        'Module' => 'CG\Skeleton\Module\Config',
        'Environment' => 'CG\Skeleton\DevelopmentEnvironment\Config'
    );

    public function __construct(array $config, $allowModifications = false)
    {
        foreach ($config as $entry => $value) {
            if (!isset($this->classMap[$entry])) {
                continue;
            }
            $config[$entry] = new $this->classMap[$entry]($value, $allowModifications);
        }
        parent::__construct($config, $allowModifications);
    }

    public function getProjectBasePath()
    {
        return rtrim(getenv(static::PROJECT_BASE_PATH) ?: '', '/');
    }

    public function setProjectBasePath($projectBasePath)
    {
        $_SERVER[static::PROJECT_BASE_PATH] = $projectBasePath;

        $export = static::PROJECT_BASE_PATH . '=' . $projectBasePath;
        exec('echo "\n#CONFIGURED BY SKELETON:\nexport ' . $export . '" >> ~/.bash_profile');
        putenv($export);

        return $this;
    }

    public function getInfrastructurePath()
    {
        $infrastructurePath = $this->get(static::INFRASTRUCTURE_PATH);
        if ($infrastructurePath) {
            return $infrastructurePath;
        }
        return $this->getProjectBasePath() . '/cginfrastructure-v4';
    }

    public function setInfrastructurePath($infrastructurePath)
    {
        $this->offsetSet(static::INFRASTRUCTURE_PATH, $infrastructurePath);
        return $this;
    }

    public function getBranch()
    {
        return $this->get(static::BRANCH);
    }

    public function setBranch($branch)
    {
        $this->offsetSet(static::BRANCH, $branch);
        return $this;
    }

    public function getNode()
    {
        return $this->get(static::NODE);
    }

    public function setNode($node)
    {
        $this->offsetSet(static::NODE, $node);
        return $this;
    }

    public function getAppName()
    {
        return $this->get(static::APP_NAME);
    }

    public function setAppName($appName)
    {
        $this->offsetSet(static::APP_NAME, $appName);
        return $this;
    }

    public function getVmPath()
    {
        return rtrim($this->get(static::VM_PATH), '/');
    }

    public function setVmPath($vmPath)
    {
        $this->offsetSet(static::VM_PATH, $vmPath);
        return $this;
    }

    public function getRole()
    {
        return $this->get(static::ROLE);
    }

    public function setRole($role)
    {
        $this->offsetSet(static::ROLE, $role);
        return $this;
    }

    public function getEnvironment()
    {
        return $this->get(static::ENVIRONMENT, 'Local');
    }

    public function setEnvironment($environment)
    {
        $this->offsetSet(static::ENVIRONMENT, $environment);
        return $this;
    }
}