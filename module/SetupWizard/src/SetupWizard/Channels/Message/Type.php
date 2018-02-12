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
I can see you're trying to connect %s, do you need any help at all? Let me know"
            ],
            ChannelIntegrationType::THIRD_PARTY => [
                'subject' => 'Hey',
                'message' => "Hey there,
I can see you're trying to connect your Magento store, can you tell me which version you use and I'll help you get connected ASAP"
            ],
            ChannelIntegrationType::UNSUPPORTED => [
                'subject' => 'Hey',
                'message' => "Hey there,
I can see you're trying to connect a channel not listed here, can you tell me which one you're looking for and I'll help you get connected ASAP"
            ],
        ],
    ];

    protected $fromIntercomId;

    public function __construct(string $fromIntercomId)
    {
        $this->fromIntercomId = $fromIntercomId;
    }

    /**
     * @throws \CG\Stdlib\Exception\Runtime\NotFound
     */
    public function getIntegrationMessage(string $channelIntegrationType): array
    {
        if (!isset($this->messages[static::INTEGRATION_MESSAGE][$channelIntegrationType])) {
            throw new NotFound(sprintf('Message has not been found for selected channel type %s.', $channelIntegrationType));
        }

        return $this->messages[static::INTEGRATION_MESSAGE][$channelIntegrationType];
    }

    protected function addFromField(array $data): array
    {
        $data['from'] = $this->fromIntercomId;

        return $data;
    }

    /**
     * @throws \CG\Stdlib\Exception\Runtime\NotFound
     */
    public function parseFields(string $channelIntegrationType, string $channelPrintName): array
    {
        $data = $this->getIntegrationMessage($channelIntegrationType);
        $data = $this->addFromField($data);

        if ($channelIntegrationType == ChannelIntegrationType::CLASSIC) {
            $data['message'] = sprintf($data['message'], $channelPrintName);
        }

        return $data;
    }
}