<?php
namespace CG\ShipStation\Carrier\BookingOption;

use CG\ShipStation\Carrier\BookingOptionInterface;
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

    public function __invoke(string $channel): BookingOptionInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className);
        if (!$class instanceof BookingOptionInterface) {
            throw new \RuntimeException($className . ' does not implement ' . BookingOptionInterface::class);
        }
        return $class;
    }
}