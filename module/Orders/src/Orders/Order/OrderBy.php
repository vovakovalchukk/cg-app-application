<?php
namespace Orders\Order;

class OrderBy
{
    protected $column;
    protected $direction;

    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setDirection($direction)
    {
        $this->direction = strtoupper($direction);
        return $this;
    }

    public function getDirection()
    {
        return $this->direction;
    }
} 