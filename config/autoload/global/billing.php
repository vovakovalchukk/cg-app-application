<?php
use CG\Billing\Package\Storage\Api as PackageApiStorage;
use CG\Billing\Package\StorageInterface as PackageStorage;
use CG\Billing\PricingSchemeAssignment\Storage\Api as PricingSchemeAssignmentApiStorage;
use CG\Billing\PricingSchemeAssignment\StorageInterface as PricingSchemeAssignmentStorage;
use CG\Billing\Subscription\Package\Storage\Api as SubscriptionPackageApiStorage;
use CG\Billing\Subscription\Package\StorageInterface as SubscriptionPackageStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                PricingSchemeAssignmentStorage::class => PricingSchemeAssignmentApiStorage::class,
                SubscriptionPackageStorage::class => SubscriptionPackageApiStorage::class,
                PackageStorage::class => PackageApiStorage::class,
            ],
            PricingSchemeAssignmentApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ],
            ],
            SubscriptionPackageApiStorage::class => [
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