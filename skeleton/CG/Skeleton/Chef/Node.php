<?php
namespace CG\Skeleton\Chef;

class Node
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
        $this->data = array(
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

    public function addToRunList($run)
    {
        if (isset(array_flip($this->data['run_list'])[$run])) {
            return $this;
        }

        $this->data['run_list'][] = $run;
        return $this;
    }

    public function setKey($key, $value, $merge = true)
    {
        $data =& $this->data;
        foreach (explode('|', $key) as $currentKey) {
            if (!isset($data[$currentKey])) {
                $data[$currentKey] = array();
            }
            $data =& $data[$currentKey];
        }

        if ($merge && is_array($value)) {
            $data = array_merge($data, $value);
        } else {
            $data = $value;
        }
    }

    public function removeKey($key)
    {
        $data =& $this->data;
        $keys = explode('|', $key);
        $key = array_pop($keys);

        foreach ($keys as $currentKey) {
            if (!isset($data[$currentKey])) {
                return;
            }
            $data =& $data[$currentKey];
        }

        unset($data[$key]);
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}