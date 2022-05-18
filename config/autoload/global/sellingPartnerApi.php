<?php
use CG\Amazon\Authorisation\AccessToken\Storage as AccessTokenStorage;
use CG\Amazon\Authorisation\AccessToken\Service as AccessTokenService;
use CG\Amazon\Authorisation\Service as AuthorisationService;

return [
    'di' => [
        'instance' => [
            AccessTokenStorage::class => [
                'parameter' => [
                    'predisClient' => 'reliable_redis'
                ],
            ],
            AccessTokenService::class => [
                'parameter' => [
                    'configuration' => 'sellingPartnerApiConfig'
                ],
            ],
            AuthorisationService::class => [
                'parameter' => [
                    'configuration' => 'sellingPartnerApiConfig'
                ],
            ],
        ]
    ]
];