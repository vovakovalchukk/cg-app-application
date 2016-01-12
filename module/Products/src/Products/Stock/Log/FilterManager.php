<?php
namespace Products\Stock\Log;

use CG\Stock\Audit\Combined\Filter;
use Products\Controller\StockLogController;
use Zend\Session\ManagerInterface;

class FilterManager
{
    protected $filter;
    protected $persistentStorage;

    public function __construct(Filter $filter, ManagerInterface $persistentStorage)
    {
        $this
            ->setFilter($filter)
            ->setPersistentStorage($persistentStorage);
    }

    public function setPersistentFilter(Filter $filter)
    {
        $storage = $this->persistentStorage->getStorage();
        $filterType = StockLogController::FILTER_TYPE;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        $storage[$filterType]['filter'] = $filter;

        return $this;
    }

    public function getPersistentFilter()
    {
        $storage = $this->persistentStorage->getStorage();
        $filterType = StockLogController::FILTER_TYPE;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        if (!isset($storage[$filterType]['filter']) || !($storage[$filterType]['filter'] instanceof Filter)) {
            $storage[$filterType]['filter'] = $this->filter;
        }

        return $storage[$filterType]['filter'];
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
     * @return self
     */
    protected function setPersistentStorage(ManagerInterface $persistentStorage)
    {
        $this->persistentStorage = $persistentStorage;
        return $this;
    }
}