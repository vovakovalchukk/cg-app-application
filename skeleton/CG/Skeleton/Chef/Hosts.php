<?php
namespace CG\Skeleton\Chef;

class Hosts
{
    protected $path;
    protected $data;

    public function __construct($path, $environmentName)
    {
        $this->path = $path;
        $this->data = array("id" => $environmentName, "hosts" => array());
        $this->load();
    }

    public function getData()
    {
        return $this->data;
    }

    protected function load()
    {
        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($this->path), true);
        if (!is_array($jsonData)) {
            return;
        }

        $this->data = array_merge($this->data, $jsonData);
    }

    public function setHost($host, $hostname, $ip)
    {
        $this->data['hosts'][$host] = array(
            'hostname' => $hostname,
            'ip' => $ip
        );
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}