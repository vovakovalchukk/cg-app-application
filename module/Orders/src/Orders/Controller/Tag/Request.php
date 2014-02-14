<?php
namespace Orders\Controller\Tag;

class Request
{
    protected $tag;
    protected $orderIds;

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setOrderIds(array $orderIds)
    {
        $this->orderIds = $orderIds;
        return $this;
    }

    public function getOrderIds()
    {
        return $this->orderIds;
    }
} 