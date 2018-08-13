<?php
namespace CG\ShipStation\Carrier\AccountDecider;

use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\GetClassNameForChannelTrait;
use Zend\Di\Di;

class Factory
{
    use GetClassNameForChannelTrait;

    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(string $channel): AccountDeciderInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className);
        if (!$class instanceof AccountDeciderInterface) {
            throw new \RuntimeException($className . ' does not implement ' . AccountDeciderInterface::class);
        }
        return $class;
    }
}