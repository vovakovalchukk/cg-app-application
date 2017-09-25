<?php
namespace Reports\OrderCount\Strategy;

use CG\Di\Di;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Factory implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'OrderCountStrategyFactory';

    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getStrategy(string $strategy): StrategyInterface
    {
        $class = $this->getClassNameByStrategyName($strategy);
        if (!class_exists($class)) {
            $this->logWarning('Strategy %s doesn\'t exist, using NullNotifier.', [$class], static::LOG_CODE);
            $class = NullStrategy::class;
        }

        return $this->di->get($class);
    }

    protected function getClassNameByStrategyName(string $strategy)
    {
        return __NAMESPACE__ . '\\' . ucfirst($strategy);
    }
}
