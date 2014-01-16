<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Vagrant\NodeData;
use CG\Skeleton\Vagrant\NodeData\Node;
use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Vagrant\Environment as EnvironmentFile;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const DEFAULT_RAM = '384';
    const DEFAULT_BOX = 'cg-precise64-php55';
    const ENVIRONMENT_PATH = 'data/environment.json';

    protected $console;
    protected $nodeData;
    protected $defaults;

    public function __construct(Startup $console, NodeData $nodeData)
    {
        $this->setConsole($console)->setNodeData($nodeData);
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

    public function setNodeData(NodeData $nodeData)
    {
        $this->nodeData = $nodeData;
        return $this;
    }

    public function getNodeData()
    {
        return $this->nodeData;
    }

    protected function runCommands(Arguments $arguments, SkeletonConfig $config, Environment $environment)
    {
        $vagrantConfig = $config->get('Vagrant', new Config($this->defaults, true));
        $this->saveNodeData($config, $vagrantConfig, $environment);
        $config->offsetSet('Vagrant', $vagrantConfig);
        $this->saveEnvironment($environment);
    }

    protected function saveNodeData(SkeletonConfig $config, Config $vagrantConfig, Environment $environment)
    {
        $nodeData = $this->getNodeData();
        $node = $nodeData->getNode($environment->getEnvironmentConfig()->getNode());

        $this->setVmRam($nodeData, $node, $config, $vagrantConfig);
        $this->setBox($nodeData, $node, $config, $vagrantConfig);
        $this->setApplication($nodeData, $node, $config, $vagrantConfig);

        $nodeData->save();

        exec(
            'git add ' . $nodeData->getPath() . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node data for ' . $environment->getEnvironmentConfig()->getNode() . '" --only -- ' . $nodeData->getPath()
        );
    }

    protected function setVmRam(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {
        $vmRam = $vagrantConfig->getVmRam();
        while (!$vmRam) {
            $this->getConsole()->writeErrorStatus('VM Ram is not set');
            $vmRam = $this->getConsole()->ask('Please enter amount of Ram to assign VM', static::DEFAULT_RAM);
        }

        $this->getConsole()->writeStatus('VM Ram set as \'' . $vmRam . '\'');
        $node->setVmRam($vmRam);
        $vagrantConfig->setVmRam($vmRam);
    }

    protected function setBox(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {
        $box = $vagrantConfig->getBox();
        while (!$box) {
            $this->getConsole()->writeErrorStatus('Vagrant box is not selected');
            $box = $this->getConsole()->ask('Please enter vagrant box to use', static::DEFAULT_BOX);
        }

        $this->getConsole()->writeStatus('Vagrant box is set as \'' . $box . '\'');
        $node->setBox($box);
        $vagrantConfig->setBox($box);
    }

    protected function setApplication(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {
        $node->addApplication(
            $config->getAppName(),
            array(
                'localDirectory' => PROJECT_NAME,
                'role' => $config->getRole()
            )
        );
    }

    protected function saveEnvironment(Environment $environment)
    {
        $environmentFile = new EnvironmentFile(static::ENVIRONMENT_PATH, $environment->getName());
        $environmentFile->save();
    }
}