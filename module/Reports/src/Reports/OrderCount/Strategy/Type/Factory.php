<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;

class Factory
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getStrategyType(string $type): TypeInterface
    {
        $class = $this->getClassNameByStrategyName($type);
        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        return $this->di->get($class);
    }

    protected function getClassNameByStrategyName(string $type)
    {
        return __NAMESPACE__ . '\\' . ucfirst($type);
    }
}
