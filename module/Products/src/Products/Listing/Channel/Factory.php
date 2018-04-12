<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;
use CG\Di\Di;
use Products\Listing\Exception as ListingException;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function buildChannelService(string $channel, array $postData = [])
    {
        $channelServiceName = __NAMESPACE__ . '\\' . hyphenToClassname($channel) . '\\' . 'Service';
        if (!class_exists($channelServiceName)) {
            throw new \InvalidArgumentException('The class: ' . $channelServiceName . ' could not be found for the provided channel: ' . $channel);
        }
        $params = !empty($postData) ? ['postData' => $postData] : [];
        return $this->di->newInstance($channelServiceName, $params);
    }

    public function fetchAndValidateChannelService($accountOrChannel, string $className, array $postData = [])
    {
        $channel = (($accountOrChannel instanceof Account) ? $accountOrChannel->getChannel() : $accountOrChannel);
        try {
            $service = $this->buildChannelService($channel, $postData);
            if ($service instanceof $className) {
                return $service;
            }
            throw new ListingException('The ' . $channel . ' account  does not support this action.');
        } catch (\InvalidArgumentException $e) {
            throw new ListingException('The ' . $channel . ' account  is not valid');
        }
    }
}
