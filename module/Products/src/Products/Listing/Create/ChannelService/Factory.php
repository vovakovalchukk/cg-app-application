<?php
namespace Products\Listing\Create\ChannelService;

use CG\Di\Di;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function buildChannelService(string $channel): ServiceInterface
    {
        $channelServiceName = __NAMESPACE__ . '\\' . hyphenToClassname($channel);
        if (!class_exists($channelServiceName)) {
            throw new \InvalidArgumentException('The class: ' . $channelServiceName . ' could not be found for the provided channel: ' . $channel);
        }
        return $this->di->newInstance($channelServiceName);
    }
}
