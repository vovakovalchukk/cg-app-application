<?php
namespace CG\Skeleton;

class Console
{
    const COLOR_RESET = "\033[0m";
    const COLOR_BLACK = "\033[0;30m";
    const COLOR_DARK_GRAY = "\033[1;30m";
    const COLOR_RED = "\033[0;31m";
    const COLOR_LIGHT_RED = "\033[1;31m";
    const COLOR_GREEN = "\033[0;32m";
    const COLOR_LIGHT_GREEN = "\033[1;32m";
    const COLOR_BROWN = "\033[0;33m";
    const COLOR_YELLOW = "\033[1;33m";
    const COLOR_BLUE = "\033[0;34m";
    const COLOR_LIGHT_BLUE = "\033[1;34m";
    const COLOR_PURPLE = "\033[0;35m";
    const COLOR_LIGHT_PURPLE = "\033[1;35m";
    const COLOR_CYAN = "\033[0;36m";
    const COLOR_LIGHT_CYAN = "\033[1;36m";
    const COLOR_LIGHT_GRAY = "\033[0;37m";
    const COLOR_WHITE = "\033[1;37m";
    const CLEAR = "\033[0;0H\033[2J";

    protected $inStream;
    protected $outStream;
    protected $errStream;

    public function __construct($inStream = null, $outStream = null, $errStream = null)
    {
        $this->inStream = $inStream ?: STDIN;
        $this->outStream = $outStream ?: STDOUT;
        $this->errStream = $errStream ?: STDERR;
    }

    public function write($string)
    {
        fwrite($this->outStream, $string);
    }

    public function writeErr($string)
    {
        fwrite($this->errStream, static::COLOR_RED . $string . static::COLOR_RESET);
    }

    public function writeln($string = '')
    {
        $this->write($string . PHP_EOL);
    }

    public function writelnErr($string = '')
    {
        $this->writeErr($string . PHP_EOL);
    }

    public function readln()
    {
        do {
            $read = array($this->inStream);
            $write = null;
            $except = array($this->inStream);
        } while(@stream_select($read, $write, $except, 0, 200000) === 0);

        return trim(fgets($this->inStream));
    }

    public function ask($question, $default = null)
    {
        $this->write(static::COLOR_BLUE . $question . static::COLOR_LIGHT_GREEN . ($default ? ' [' . $default . ']' : '') . ': ' . static::COLOR_RESET);
        return $this->readln() ?: $default;
    }

    public function askWithOptions($question, array $options, $default = null)
    {
        if (empty($options)) {
            return;
        }

        $options = array_combine(
            array_map('strtolower', $options),
            $options
        );

        if (!isset($options[strtolower($default)])) {
            $options[strtolower($default)] = $default;
        }

        $displayOptions = array_map(
            function($option) use ($default) {
                return (strcasecmp($option, $default) == 0 ? strtoupper($option) : $option);
            },
            array_keys($options)
        );

        $this->write(
            static::COLOR_BLUE . $question
            . static::COLOR_LIGHT_GREEN . ' [' . implode(',', $displayOptions) . ']' . ': '
            . static::COLOR_RESET
        );

        $option = strtolower($this->readln() ?: $default);
        if (!isset($options[$option]) && !$default) {
            $this->askWithOptions($question, $options, $default);
        }
        return $options[$option];
    }

    public function clear()
    {
        $this->write(static::CLEAR);
    }
}