<?php
namespace CG\Skeleton\Vagrant;

class Environment
{
    protected $path;
    protected $data;

    public function __construct($path, $environmentName)
    {
        $this->path = $path;
        $this->data = array();
        $this->setEnvironment($environmentName);
    }

    public function setEnvironment($environmentName)
    {
        $this->data['current_environment'] = $environmentName;
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}