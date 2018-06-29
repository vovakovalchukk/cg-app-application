<?php
namespace ShipStation\Setup;

use ShipStation\SetupInterface;
use ShipStation\Setup\Other;
use function CG\Stdlib\hyphenToClassname;
use Zend\Di\Di;

class Factory
{
    /** @var DI */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(string $channel): ?SetupInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className);
        if (!$class instanceof SetupInterface) {
            throw new \RuntimeException($className . ' does not implement ' . SetupInterface::class);
        }
        return $class;
    }

    protected function getClassNameForChannel(string $channel)
    {
        return hyphenToClassname(preg_replace('/-ss$/', '', $channel));
    }
}