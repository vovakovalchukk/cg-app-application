<?php
namespace CG\Skeleton;

interface CommandInterface
{
    public function getName();
    public function run(Arguments $arguments, Config $config);
}