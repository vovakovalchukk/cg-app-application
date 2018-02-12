<?php
namespace SetupWizard\Channels\Message;

use CG\Channel\Integration\Type as ChannelIntegrationType;
use CG\Stdlib\Exception\Runtime\NotFound;

class Type
{
    const INTEGRATION_MESSAGE = 'integrationMessage';

    protected $messages = [
        self::INTEGRATION_MESSAGE => [
            ChannelIntegrationType::CLASSIC => [
                'subject' => 'Hey',
                'message' => "Hey there, 
I can see you're trying to connect %s, do you need any help at all? Let me know",
                'from' => 1222805 //dj's intercom id
            ],
            ChannelIntegrationType::THIRD_PARTY => [
                'subject' => 'Hey',
                'message' => "Hey there,
I can see you're trying to connect your Magento store, can you tell me which version you use and I'll help you get connected ASAP",
                'from' => 1222805 //dj's intercom id
            ],
            ChannelIntegrationType::UNSUPPORTED => [
                'subject' => 'Hey',
                'message' => "Hey there,
I can see you're trying to connect a channel not listed here, can you tell me which one you're looking for and I'll help you get connected ASAP",
                'from' => 1222805 //dj's intercom id
            ],
        ],
    ];

    public function __construct(string $fromIntercomId)
    {

    }

    /**
     * @param string $channelintegrationType
     * @throws \CG\Stdlib\Exception\Runtime\NotFound
     * @return array
     */
    public function getIntegrationMessage(string $channelintegrationType): array
    {
        if (!isset($this->messages[static::INTEGRATION_MESSAGE][$channelintegrationType])) {
            throw new NotFound(sprintf('Message has not been found for selected channel type %s.', $channelintegrationType));
        }

        return $this->messages[static::INTEGRATION_MESSAGE][$channelintegrationType];
    }
}