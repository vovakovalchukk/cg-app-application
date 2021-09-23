<?php
namespace CG\UkMail\CustomsDeclaration;

use Zend\Di\Di;

class Factory
{
    protected $di;
    protected $factories = [];

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(string $type): FactoryInterface
    {
        if (isset($this->factories[$type])) {
            return $this->factories[$type];
        }

        $className = $this->getClassnameFromType($type);
        if (!class_exists($className)) {
            throw new \RuntimeException($className.' does not exist');
        }

        $factoryClass = $this->di->newInstance($className);
        if (!($factoryClass instanceof FactoryInterface)) {
            throw new \RuntimeException($className.' is not an instance of '.FactoryInterface::class);
        }

        $this->factories[$type] = $factoryClass;
        return $factoryClass;
    }

    protected function getClassnameFromType(string $type): string
    {
        return "CG\\UkMail\\CustomsDeclaration\\Factory\\" . \CG\Stdlib\hyphenToClassname($type);
    }
}