<?php
use CG\Channel\Creation\SetupViewInterface;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use CG_Shopify\Account\Service as ShopifyService;
use SetupWizard\Channels\ConnectViewFactory;

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
        ],
    ]
];
