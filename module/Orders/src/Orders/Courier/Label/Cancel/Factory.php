<?php
namespace Orders\Courier\Label\Cancel;

use Zend\Di\Di;

class Factory
{
    protected $di;
    protected $channels = [];

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(string $channelName): CancelActionInterface
    {
        if (isset($this->channels[$channelName])) {
            return $this->channels[$channelName];
        }

        $className = 'Orders\\Courier\\Label\\Cancel\\Action\\' . ucfirst($channelName);
        if (!class_exists($className)) {
            $className = DefaultAction::class;
        }

        $channelAction = $this->di->newInstance($className);
        if (!($channelAction instanceof CountryInterface)) {
            throw new \RuntimeException($className.' not an instance of '.CancelActionInterface::class);
        }

        $this->channels[$channelName] = $channelAction;
        return $channelAction;
    }
}