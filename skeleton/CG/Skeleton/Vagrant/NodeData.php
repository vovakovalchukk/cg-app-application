<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\Vagrant\NodeData\Node;

class NodeData
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    protected function load($reload = false)
    {
        if (is_array($this->data) && $reload == false) {
            return;
        }

        $this->data = array();

        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($this->path), true);
        if (!is_array($jsonData)) {
            return;
        }

        foreach ($jsonData as $node => $nodeData) {
            $this->data[$node] = new Node($nodeData);
        }
    }

    public function getNode($node)
    {
        $this->load();
        if (!isset($this->data[$node])) {
            $this->data[$node] = new Node();
        }

        $nodeData =& $this->data[$node];
        return $nodeData;
    }

    public function getIpsInUse()
    {
        $this->load();
        $ips = array();
        foreach ($this->data as $node => $nodeData) {
            $ip = $nodeData->getVmIp();
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                continue;
            }
            $ips[$node] = $ip;
        }
        return $ips;
    }

    public function save()
    {
        $this->load();
        $data = array();
        foreach ($this->data as $node => $nodeData) {
            $data[$node] = $nodeData->getArrayCopy();
        }
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }
}