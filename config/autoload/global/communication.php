<?php

use CG\Communication\Headline\StorageInterface as HeadlineStorage;
use CG\Communication\Headline\Storage\Api as HeadlineApi;
use CG\Communication\Message\StorageInterface as MessageStorage;
use CG\Communication\Message\Storage\Api as MessageApi;
use CG\Communication\Thread\StorageInterface as ThreadStorage;
use CG\Communication\Thread\Storage\Api as ThreadApi;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ThreadStorage::class => ThreadApi::class,
                MessageStorage::class => MessageApi::class,
                HeadlineStorage::class => HeadlineApi::class,
            ],
            ThreadApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            MessageApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            HeadlineApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
        ]
    ]
];