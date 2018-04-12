<?php

use CG\FileStorage\S3\Adapter as FileStorageS3Adapter;
use CG\Image\Uploader as ImageUploader;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'ImageUploaderFileStorage' => FileStorageS3Adapter::class,
            ],
            'ImageUploaderFileStorage' => [
                'parameters' => [
                    'location' => function() { return ImageUploader::BUCKET; }
                ]
            ],
            ImageUploader::class => [
                'parameters' => [
                    'fileStorage' => 'ImageUploaderFileStorage',
                ]
            ],
        ]
    ]
];