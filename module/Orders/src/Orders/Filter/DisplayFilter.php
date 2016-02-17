<?php
namespace Orders\Filter;

use CG\Order\Service\Filter;

class DisplayFilter
{
    /** @var array $display */
    protected $display;
    /** @var Filter $filter */
    protected $filter;

    public  function __construct(array $display, Filter $filter)
    {
        $this->setDisplay($display)->setFilter($filter);
    }

    public function __isset($name)
    {
        return isset($this->display[$name]);
    }

    public function __call($name, $arguments)
    {
        $callable = [$this->filter, $name];
        if (is_callable($callable)) {
            return call_user_func_array($callable, $arguments);
        }
        return null;
    }

    /**
     * @return self
     */
    protected function setDisplay(array $display)
    {
        $this->display = array_fill_keys($display, true);
        return $this;
    }

    /**
     * @return self
     */
    protected function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }
} 
