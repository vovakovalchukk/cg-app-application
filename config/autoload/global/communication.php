<?php

use CG\Communication\Headline\Storage\Api as HeadlineApi;
use CG\Communication\Headline\StorageInterface as HeadlineStorage;
use CG\Communication\Message\Attachment\Storage\Api as AttachmentApi;
use CG\Communication\Message\Attachment\StorageInterface as AttachmentStorage;
use CG\Communication\Message\Storage\Api as MessageApi;
use CG\Communication\Message\StorageInterface as MessageStorage;
use CG\Communication\Message\Template\Storage\Api as MessageTemplateApi;
use CG\Communication\Message\Template\StorageInterface as MessageTemplateStorage;
use CG\Communication\Thread\Storage\Api as ThreadApi;
use CG\Communication\Thread\StorageInterface as ThreadStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                ThreadStorage::class => ThreadApi::class,
                MessageStorage::class => MessageApi::class,
                MessageTemplateStorage::class => MessageTemplateApi::class,
                HeadlineStorage::class => HeadlineApi::class,
                AttachmentStorage::class => AttachmentApi::class
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
            MessageTemplateApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            HeadlineApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
            AttachmentApi::class => [
                'parameters' => [
                    'client' => 'communication_guzzle'
                ]
            ],
        ]
    ]
];