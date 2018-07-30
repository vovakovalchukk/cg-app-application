<?php

use CG\Ebay\CatalogApi\Token\InitialisationService;

return [
    'di' => [
        'instance' => [
            InitialisationService::class => [
                'parameters' => [
                    'clientId' => 'ChannelG-0071-4f96-ab31-52ce80da6385',
                    'ruName' => 'ChannelGrabber_-ChannelG-0071-4-zsymcbi',
                    'authBaseUrl' => 'https://auth.sandbox.ebay.com/oauth2/authorize'
                ],
            ],
        ]
    ]
];