<?php
namespace CG\ShipStation\Carrier\Label\Creator;

use CG\ShipStation\Carrier\Label\CreatorInterface;
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

    public function __invoke(string $channel): CreatorInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className, ['guzzleClient' => $this->guzzleClient]);
        if (!$class instanceof CreatorInterface) {
            throw new \RuntimeException($className . ' does not implement ' . CreatorInterface::class);
        }
        return $class;
    }
}