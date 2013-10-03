<?php
namespace CG\Skeleton;

class Arguments
{
    protected $options = '';
    protected $longopts = array();
    protected $arguments;

    public function __construct()
    {
        $this->arguments = getopt($this->options, $this->longopts);
    }
}