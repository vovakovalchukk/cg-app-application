<?php
namespace CG\ShipStation\Carrier\CarrierSpecificData;

use CG\ShipStation\Carrier\CarrierSpecificDataInterface;
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

    public function __invoke(string $channel): CarrierSpecificDataInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className);
        if (!$class instanceof CarrierSpecificDataInterface) {
            throw new \RuntimeException($className . ' does not implement ' . CarrierSpecificDataInterface::class);
        }
        return $class;
    }
}