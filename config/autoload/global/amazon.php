<?php
use CG\Amazon\Region\Service as AmazonRegionService;

return [
    'di' => [
        'instance' => [
            AmazonRegionService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
        ],
    ],
];