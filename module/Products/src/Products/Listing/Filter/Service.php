<?php
namespace Products\Listing\Filter;

use CG\Listing\Unimported\Filter;
use Products\Controller\ListingsController;
use Zend\Session\ManagerInterface;

class Service
{
    protected $filter;
    protected $persistentStorage;

    public function __construct(Filter $filter, ManagerInterface $persistentStorage)
    {
        $this
            ->setFilter($filter)
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
        $filterType = ListingsController::FILTER_TYPE;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        $storage[$filterType]['filter'] = $filter;

        return $this;
    }

    public function getPersistentFilter()
    {
        $storage = $this->getPersistentStorage()->getStorage();
        $filterType = ListingsController::FILTER_TYPE;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        if (!isset($storage[$filterType]['filter']) || !($storage[$filterType]['filter'] instanceof Filter)) {
            $storage[$filterType]['filter'] = $this->getFilter();
        }

        return $storage[$filterType]['filter'];
    }
}