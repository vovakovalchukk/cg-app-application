<?php
namespace CG\Skeleton\Console;

use CG\Skeleton\Console;

class Shutdown extends Console
{
    protected $newLine = true;
    protected $indent = '   ';

    public function write($string)
    {
        if ($this->newLine) {
            parent::write($this->indent);
        }
        $this->newLine = false;
        parent::write($string);
    }

    public function writeln($string = '')
    {
        $this->newLine = true;
        parent::writeln($string);
    }

    public function writeStatus($string)
    {
        $this->newLine = false;
        parent::writeln(static::COLOR_PURPLE . ' + ' . $string . static::COLOR_RESET);
        $this->newLine = true;
    }

    public function writeErrorStatus($string)
    {
        $this->newLine = false;
        parent::writeln(static::COLOR_RED . ' - ' . $string . static::COLOR_RESET);
        $this->newLine = true;
    }
}