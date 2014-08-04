<?php
namespace Products\Filter;

use CG\Order\Service\Filter;
use CG\Order\Service\Filter\Mapper;
use Zend\Session\ManagerInterface;

class Service
{
    protected $filter;
    protected $mapper;
    protected $persistentStorage;

    public function __construct(Filter $filter, Mapper $mapper, ManagerInterface $persistentStorage)
    {
        $this
            ->setFilter($filter)
            ->setMapper($mapper)
            ->setPersistentStorage($persistentStorage);
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setPersistentStorage(ManagerInterface $persistentStorage)
    {
        $this->persistentStorage = $persistentStorage;
        return $this;
    }

    public function getPersistentStorage()
    {
        return $this->persistentStorage;
    }

    public function setPersistentFilter(Filter $filter)
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['products'])) {
            $storage['products'] = [];
        }

        $storage['products']['filter'] = $filter;

        return $this;
    }

    public function getPersistentFilter()
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['products'])) {
            $storage['products'] = [];
        }

        if (!isset($storage['products']['filter']) || !($storage['products']['filter'] instanceof Filter)) {
            $storage['products']['filter'] = $this->getFilter();
        }

        return $storage['products']['filter'];
    }

    public function getFilterFromArray(array $data)
    {
        return $this->getMapper()->fromArray($data);
    }

    public function mergeFilters(Filter $filter1, Filter $filter2)
    {
        return $this->getMapper()->merge($filter1, $filter2);
    }
}