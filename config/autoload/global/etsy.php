<?php
use CG\Etsy\Account;
use CG\Etsy\Account\CreationService;
use CG\Etsy\Client\Factory as ClientFactory;

return [
    'di' => [
        'instance' => [
            CreationService::class => [
                'parameters' => [
                    'cryptor' => 'etsy_cryptor',
                    'channelAccount' => Account::class,
                ],
            ],
//            ClientFactory::class => [
//                'parameters' => [
//                    'cryptor' => 'etsy_cryptor',
//                ],
//            ],
        ]
    ]
];