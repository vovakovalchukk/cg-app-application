<?php
use CG\Channel\Creation\SetupViewInterface;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use Shopify\Account\Service as ShopifyService;
use SetupWizard\Channels\ConnectViewFactory;
use SetupWizard\Channels\Message\Type as ChannelsMessageType;

return [
    'di' => [
        'definition' => [
            'class' => [
                ConnectViewFactory::class => [
                    'methods' => [
                        'addChannelService' => [
                            'channel' => ['required' => true],
                            'region' => ['required' => true],
                            'service' => [
                                'type' => SetupViewInterface::class,
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            ConnectViewFactory::class => [
                'injections' => [
                    'addChannelService' => [
                        ['channel' => ShopifyAccountCreator::CHANNEL, 'region' => '', 'service' => ShopifyService::class],
                    ],
                ],
            ],
            ChannelsMessageType::class => [
                'parameter' => [
                    'fromIntercomId' => '1222805' //dj's intercom id
                ]
            ],
        ],
    ]
];
