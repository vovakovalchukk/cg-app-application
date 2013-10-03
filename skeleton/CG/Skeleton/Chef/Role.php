<?php
namespace CG\Skeleton\Chef;

class Role
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
        $this->data = array(
            'name' => basename($path, '.json'),
            'chef_type' => 'role',
            'json_class' => 'Chef::Role',
            'description' => 'Role for ' . basename($path, '.json'),
            'run_list' => array()
        );
        $this->load();
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

    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function setDescription($description)
    {
        $this->data['description'] = $description;
        return $this;
    }

    public function addToRunList($run)
    {
        if (isset(array_flip($this->data['run_list'])[$run])) {
            return $this;
        }

        $this->data['run_list'][] = $run;
        return $this;
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}