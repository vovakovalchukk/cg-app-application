<?php
namespace Products\Product;

use CG_UI\View\Filters;
use Zend\Config\Config;
use ArrayAccess;
use RuntimeException;
use CG\Order\Service\Filter;
use Filters\Factory;

class FilterService
{
    protected $factory;
    protected $config;
    protected $productFilters;

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

    public function setProductFilters($productFilters)
    {
        $this->productFilters = $productFilters;
        return $this;
    }

    public function getProductFilters(Filter $filterValues)
    {
        if ($this->productFilters) {
            return $this->productFilters;
        }
        $this->getFactory()->setFilterValues($filterValues);
        return $this->productFilters = $this->getFactory()->create(
            $this->getFilterConfig('products')
        );
    }

    public function getFilterNames()
    {
        $names = [];
        $filters = $this->getConfig()["products"]["rows"];
        foreach (array_merge($filters[0]["filters"], $filters[1]["filters"]) as $filter) {
            if (isset($filter['variables']['name'])) {
                $names[]  = $filter['variables']['name'];
            }
        }
        return $names;
    }
}
