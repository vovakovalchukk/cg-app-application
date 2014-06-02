<?php
namespace Orders\Order;

class PageLimit
{
    protected $page = 1;
    protected $limit = 'all';

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setPageFromOffset($offset)
    {
        $this->page = ceil($offset / $this->getLimit());
    }
} 