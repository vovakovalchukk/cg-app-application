<?php
namespace Products\Stock\Log;

use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Type;
use Products\Controller\StockLogController;
use Zend\Session\ManagerInterface;

class FilterManager
{
    const DEFAULT_DATE_FROM = '-7 days  00:00:00';
    const DEFAULT_DATE_TO = '23:59:59';
    const DEFAULT_DATE_PERIOD = 'Last 7 days';

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
        $filterType = StockLogController::FILTER_PRODUCT_LOGS;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        $storage[$filterType]['filter'] = $filter;

        return $this;
    }

    public function getPersistentFilter()
    {
        $storage = $this->persistentStorage->getStorage();
        $filterType = StockLogController::FILTER_PRODUCT_LOGS;

        if (!isset($storage[$filterType])) {
            $storage[$filterType] = [];
        }

        if (!isset($storage[$filterType]['filter']) || !($storage[$filterType]['filter'] instanceof Filter)) {
            $storage[$filterType]['filter'] = $this->filter;
        }

        return $storage[$filterType]['filter'];
    }

    public function setFilterDefaults(Filter $filter)
    {
        if (!$filter->getDateTimeFrom() && !$filter->getDateTimeTo()) {
            $filter->setDateTimeFrom(static::DEFAULT_DATE_FROM)
                ->setDateTimeTo(static::DEFAULT_DATE_TO)
                ->setDateTimePeriod(static::DEFAULT_DATE_PERIOD);
        }
        if (empty($filter->getType())) {
            $filter->setType(array_values(Type::getAllTypes()));
        }
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