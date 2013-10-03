<?php
namespace CG\Skeleton\Environment;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class StartupCommand implements StartupCommandInterface
{
    protected $console;

    public function __construct(Startup $console)
    {
        $this->setConsole($console);
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

    protected function validateBranch(Config $config, $branch)
    {
        exec(
            'cd ' . $config->getInfrastructurePath() . ';'
            . ' git fetch 2>&1;'
            . ' git ls-remote --exit-code . ' . $branch . ' 2>&1'
            . ' || git ls-remote --exit-code . origin/' . $branch . ' 2>&1',
            $output,
            $exitCode
        );

        return $exitCode == 0;
    }

    protected function currentBranch(Config $config)
    {
        ob_start();
        passthru(
            'cd ' . $config->getInfrastructurePath() . ';'
            . 'git rev-parse --abbrev-ref HEAD'
        );
        return trim(ob_get_clean());
    }

    public function run(Arguments $arguments, Config $config)
    {
        $this->setupProjectBasePath($config);
        $this->setupInfrastructurePath($config);
        $this->setupBranch($config);
        $this->setupNode($config);
        $this->setupAppName($config);
        $this->setupHostname($config);
        $this->setupVmPath($config);
    }

    protected function setupProjectBasePath(Config $config)
    {
        $saveToConfig = false;
        $projectBasePath = $config->getProjectBasePath();
        $defaultProjectBasePath = dirname(getcwd());

        while (!is_dir($projectBasePath)) {
            $saveToConfig = true;
            $this->getConsole()->writeErrorStatus('Projects Base Path is not set or is not a valid directory');
            $projectBasePath = $this->getConsole()->ask('Please enter new path', $defaultProjectBasePath);
        }

        $this->getConsole()->writeStatus('Projects Base Path set as \'' . $projectBasePath . '\'');
        if ($saveToConfig) {
            $this->getConsole()->writeStatus('Projects Base Path written to ~/.bash_profile');
            $config->setProjectBasePath($projectBasePath);
        }
    }

    protected function setupInfrastructurePath(Config $config)
    {
        $saveToConfig = false;
        $infrastructurePath = $config->getInfrastructurePath();

        while (!is_dir($infrastructurePath)) {
            $this->getConsole()->writeErrorStatus(Config::INFRASTRUCTURE_NAME . ' not found');

            $localCopy = $this->getConsole()->askWithOptions(
                'Do you have a local copy of ' . Config::INFRASTRUCTURE_NAME,
                array('y', 'n'),
                'y'
            );

            if ($localCopy == 'y') {
                $saveToConfig = true;
                $infrastructurePath = $this->getConsole()->ask('Please enter new path');
            } else {
                $saveToConfig = false;
                passthru('git clone ' . Config::INFRASTRUCTURE_REPOSITORY . ' ' . $infrastructurePath);
            }
        }

        $this->getConsole()->writeStatus('Using ' . Config::INFRASTRUCTURE_NAME . ' at \'' . $infrastructurePath . '\'');
        if ($saveToConfig) {
            $config->setInfrastructurePath($infrastructurePath);
        }
    }

    protected function setupBranch(Config $config)
    {
        $branch = $config->getBranch();
        $currentBranch = $this->currentBranch($config);

        while (!$branch || !$this->validateBranch($config, $branch)) {
            $this->getConsole()->writeErrorStatus('No ' . Config::INFRASTRUCTURE_NAME . ' branch set');

            if ($branch) {
                $newBranch = $this->getConsole()->askWithOptions(
                    'Do you want to create branch \'' . $branch . '\'',
                    array('y', 'n'),
                    'y'
                );

                if ($newBranch == 'y') {
                    passthru(
                        'cd ' . $config->getInfrastructurePath() . ';'
                        . ' git branch --no-track ' . $branch . ' ' . Config::INFRASTRUCTURE_BRANCH
                    );
                    break;
                }
            }

            $branch = $this->getConsole()->ask('Please enter branch name', $currentBranch);
        }

        $this->getConsole()->writeStatus('Using branch \'' . $branch . '\' from ' . Config::INFRASTRUCTURE_NAME);
        $config->setBranch($branch);
    }

    protected function setupNode(Config $config)
    {
        $node = $config->getNode();
        while (!$node) {
            $this->getConsole()->writeErrorStatus('No node set');
            $node = $this->getConsole()->ask('What node will this application go on');
        }
        $this->getConsole()->writeStatus('Application configured for node \'' . $node . '\'');
        $config->setNode($node);
    }

    protected function setupAppName(Config $config)
    {
        $appName = $config->getAppName();
        while (!$appName) {
            $this->getConsole()->writeErrorStatus('Application name is not set');
            $appName = $this->getConsole()->ask('What will your application be called');
        }
        $this->getConsole()->writeStatus('Application is called \'' . $appName . '\'');
        $config->setAppName($appName);
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

    protected function setupVmPath(Config $config)
    {
        $vmPath = $config->getVmPath();
        while (!$vmPath) {
            $this->getConsole()->writeErrorStatus('VM application root is not set');
            $vmPath = $this->getConsole()->ask(
                'What is the absolute path of your application root on the vm',
                '/var/www/' . $config->getAppName()
            );
        }
        $this->getConsole()->writeStatus('VM application root set to \'' . $vmPath . '\'');
        $config->setVmPath($vmPath);
    }
}