<?php
use CG\Amazon\Authorisation\AccessToken\Storage as AccessTokenStorage;
use CG\Amazon\Authorisation\AccessToken\Service as AccessTokenService;
use CG\Amazon\Authorisation\Service as AuthorisationService;
use CG\Amazon\Carrier\SpApi\CancelShipment as SpApiCancelShipment;
use CG\Amazon\Carrier\SpApi\CreateShipment as SpApiCreateShipment;
use CG\Amazon\Carrier\SpApi\GetEligibleShippingServices as SpApiGetEligibleShippingServices;
use CG\Amazon\Carrier\SpApi\GetShipment as SpApiGetShipment;
use CG\Amazon\ListingImport as AmazonListingImport;
use CG\Amazon\Marketplace\Participation\SpApi\Service as AmazonMarketplaceParticipationSpApiService;
use CG\Amazon\Marketplace\Participation\Mws\Service as AmazonMarketplaceParticipationMwsService;
use CG\Amazon\Region\Service as AmazonRegionService;
use CG\Amazon\Serializer\NameConverter\LowerCamelCaseToUpperCamelCaseNameConverter;
use SellingPartnerApi\Api\SellersApi;
use SellingPartnerApi\Configuration;
use SellingPartnerApi\Api\AuthorizationApi;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;


return [
    'di' => [
        'instance' => [
            'alias' => [
                'json_object_serializer' => Serializer::class,
            ],
            'preferences' => [
                Configuration::class => 'sellingPartnerApiConfig',
            ],
            AmazonRegionService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AmazonListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'amazonGearmanClient'
                ]
            ],
            AccessTokenStorage::class => [
                'parameter' => [
                    'predisClient' => 'reliable_redis'
                ],
            ],
            AccessTokenService::class => [
                'parameter' => [
                    'configuration' => 'sellingPartnerApiConfig',
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AuthorisationService::class => [
                'parameter' => [
                    'configuration' => 'sellingPartnerApiConfig',
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AuthorizationApi::class => [
                'parameter' => [
                    'config' => 'sellingPartnerApiConfig'
                ],
            ],
            AmazonMarketplaceParticipationSpApiService::class => [
                'parameter' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            AmazonMarketplaceParticipationMwsService::class => [
                'parameter' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            SellersApi::class => [
                'parameter' => [
                    'config' => 'sellingPartnerApiConfig',
                ],
            ],
            'json_object_serializer' => [
                'parameters' => [
                    'normalizers' => [
                        new GetSetMethodNormalizer(
                            null,
                            new LowerCamelCaseToUpperCamelCaseNameConverter()
                        ),
                    ],
                    'encoders' => [
                        JsonEncoder::FORMAT => new JsonEncoder(),
                        'xml' => new XmlEncoder(),
                    ],
                ],
            ],
            SpApiGetEligibleShippingServices::class => [
                'parameters' => [
                    'serializer' => 'json_object_serializer',
                ],
            ],
            SpApiCreateShipment::class => [
                'parameters' => [
                    'serializer' => 'json_object_serializer',
                ],
            ],
            SpApiGetShipment::class => [
                'parameters' => [
                    'serializer' => 'json_object_serializer',
                ],
            ],
            SpApiCancelShipment::class => [
                'parameters' => [
                    'serializer' => 'json_object_serializer',
                ],
            ],
        ],
    ],
];