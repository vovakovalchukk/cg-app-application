<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Vagrant\NodeData;
use CG\Skeleton\Vagrant\NodeData\Node;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const DEFAULT_RAM = '512';
    const DEFAULT_BOX = 'cg-precise64-php55';

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

    protected function runCommands(Arguments $arguments, SkeletonConfig $config)
    {
        $vagrantConfig = $config->get('Vagrant', new Config($this->defaults, true));
        $this->saveNodeData($config, $vagrantConfig);
        $config->offsetSet('Vagrant', $vagrantConfig);
    }

    protected function saveNodeData(SkeletonConfig $config, Config $vagrantConfig)
    {
        $nodeData = $this->getNodeData();
        $node = $nodeData->getNode($config->getNode());

        $this->setVmRam($nodeData, $node, $config, $vagrantConfig);
        $this->setVmIp($nodeData, $node, $config, $vagrantConfig);
        $this->setBox($nodeData, $node, $config, $vagrantConfig);
        $this->setApplication($nodeData, $node, $config, $vagrantConfig);

        $nodeData->save();

        exec(
            'git add ' . $nodeData->getPath() . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated node data for ' . $config->getNode() . '" --only -- ' . $nodeData->getPath()
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

    protected function setVmIp(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {
        $vmIp = $vagrantConfig->getVmIp();
        $vmIps = $nodeData->getIpsInUse();

        while (!filter_var($vmIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->getConsole()->writeErrorStatus('VM ip is not set or is invalid');

            if (!empty($vmIps)) {
                $this->getConsole()->writeln('The following ips are in use');
                foreach ($vmIps as $nodeName => $ip) {
                    $this->getConsole()->writeln('   * ' . $ip . ' => ' . $nodeName);
                }
                $this->getConsole()->writeln('Please remember other ips may be in use, please confer with other developers before setting an ip address');
            }

            $vmIp = $this->getConsole()->ask('What local ip address would you like to access the vm for this node', '192.168.33.21');
        }

        $this->getConsole()->writeStatus('VM ip set as \'' . $vmIp . '\'');
        $node->setVmIp($vmIp);
        $vagrantConfig->setVmIp($vmIp);

        $this->getConsole()->writeStatus(
            'Saving VM ip to /etc/hosts '
            . Startup::COLOR_PURPLE . '(You may be prompted for your password)' . Startup::COLOR_RESET
        );

        exec(
            'grep -q -e "' . $vmIp . ' ' . $config->getHostname() . '.local" /etc/hosts'
            . ' || echo "' . $vmIp . ' ' . $config->getHostname() . '.local" | sudo tee -a /etc/hosts'
        );

        $this->getConsole()->writeStatus('VM ip saved to /etc/hosts');
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
}