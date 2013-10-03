<?php
namespace CG\Skeleton;

interface StartupCommandInterface
{
    public function run(Arguments $arguments, Config $config);
}