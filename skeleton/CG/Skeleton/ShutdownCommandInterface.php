<?php
namespace CG\Skeleton;

interface ShutdownCommandInterface
{
    public function run(Arguments $arguments, Config $config);
}