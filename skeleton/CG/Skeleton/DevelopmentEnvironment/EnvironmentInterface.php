<?php
namespace CG\Skeleton\DevelopmentEnvironment;


use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Chef\Node;
use CG\Skeleton\Config as SkeletonConfig;

interface EnvironmentInterface
{
    public function getName();
    public function getSuffix();

    public function setupIp(Startup $console);
    public function setupHostsFile(Startup $console);

    public function setupNode(Startup $console);

    public function vagrantUp(Console $console);
    public function vagrantProvision(Console $console);
    public function vagrantSsh(Console $console);
    public function vagrantReload(Console $console);
    public function vagrantHalt(Console $console);

    public function getInitialNodeRunList();

    public function setDatabaseStorageKey(SkeletonConfig $config, BaseConfig $moduleConfig, Node &$node);
}