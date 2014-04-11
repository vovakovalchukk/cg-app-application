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
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getFilterConfig($filter)
    {
        $config = $this->getConfig();
        if (!isset($config['filters']) || (!is_array($config['filters']) && !($config['filters'] instanceof ArrayAccess))) {
            throw new RuntimeException('No filters Configured');
        }

        $filters = $config['filters'];
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