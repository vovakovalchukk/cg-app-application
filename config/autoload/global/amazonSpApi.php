<?php

use CG\Amazon\Carrier\SpApi\GetEligibleShippingServices as SpApiGetEligibleShippingServices;
use CG\Amazon\Carrier\SpApi\CreateShipment as SpApiCreateShipment;
use CG\Amazon\Carrier\SpApi\GetShipment as SpApiGetShipment;
use CG\Amazon\Carrier\SpApi\CancelShipment as SpApiCancelShipment;
use CG\Amazon\Serializer\NameConverter\LowerCamelCaseToUpperCamelCaseNameConverter;
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
