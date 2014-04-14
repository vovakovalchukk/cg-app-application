<?php
namespace Orders\Order;

use CG_UI\View\Filters;
use Zend\Config\Config;
use ArrayAccess;
use RuntimeException;

class FilterService
{
    protected $factory;
    protected $config;
    protected $orderFilters;

    public function __construct(Filters\Factory $factory, $config)
    {
        $this->setFactory($factory)
             ->setConfig($config);
    }

    public function setFactory(Filters\Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @return Filters\Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    public function setConfig($config)
    {
        if (isset($config['filters'])) {
            $config = $config['filters'];
        }
        if (!(is_array($config) || ($config instanceof ArrayAccess))) {
            throw new RuntimeException('Filter config must be accessible as an array');
        }
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getFilterConfig($filter)
    {
        $filters = $this->config;

        if (!isset($filters[$filter]) || !is_array($filters[$filter])) {
            throw new RuntimeException('Requested filter not Configured : ' . $filter);
        }
        return $filters[$filter];
    }

    public function setOrderFilters($orderFilters)
    {
        $this->orderFilters = $orderFilters;
        return $this;
    }

    public function getOrderFilters()
    {
        if ($this->orderFilters) {
            return $this->orderFilters;
        }

        return $this->orderFilters = $this->getFactory()->create(
            $this->getFilterConfig('orders')
        );
    }
}
