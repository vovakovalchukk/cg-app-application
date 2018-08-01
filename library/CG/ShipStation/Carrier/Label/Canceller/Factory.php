<?php
namespace CG\ShipStation\Carrier\Label\Canceller;

use CG\ShipStation\Carrier\Label\CancellerInterface;
use CG\ShipStation\GetClassNameForChannelTrait;
use Guzzle\Http\Client as GuzzleClient;
use Zend\Di\Di;

class Factory
{
    use GetClassNameForChannelTrait;

    /** @var Di */
    protected $di;
    /** @var GuzzleClient */
    protected $guzzleClient;

    public function __construct(Di $di, GuzzleClient $guzzleClient)
    {
        $this->di = $di;
        $this->guzzleClient = $guzzleClient;
    }

    public function __invoke(string $channel): CancellerInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className, ['guzzleClient' => $this->guzzleClient]);
        if (!$class instanceof CancellerInterface) {
            throw new \RuntimeException($className . ' does not implement ' . CancellerInterface::class);
        }
        return $class;
    }
}