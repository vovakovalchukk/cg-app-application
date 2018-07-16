<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\ShipStation\Carrier\Label\CreatorInterface;
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

    public function __invoke(string $channel): CreatorInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className);
        if (!$class instanceof CreatorInterface) {
            throw new \RuntimeException($className . ' does not implement ' . CreatorInterface::class);
        }
        return $class;
    }
}