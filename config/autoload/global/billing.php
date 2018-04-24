<?php
use CG\Billing\PricingSchemeAssignment\Storage\Api as PricingSchemeAssignmentApiStorage;
use CG\Billing\PricingSchemeAssignment\StorageInterface as PricingSchemeAssignmentStorage;
use CG\Billing\Subscription\Package\Storage\Api as PackageApiStorage;
use CG\Billing\Subscription\Package\StorageInterface as PackageStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                PricingSchemeAssignmentStorage::class => PricingSchemeAssignmentApiStorage::class,
                PackageStorage::class => PackageApiStorage::class,
            ],
            PricingSchemeAssignmentApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ],
            ],
            PackageApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ],
            ],
        ],
    ],
];