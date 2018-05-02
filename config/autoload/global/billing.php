<?php
use CG\Billing\Package\Storage\Api as PackageApiStorage;
use CG\Billing\Package\StorageInterface as PackageStorage;
use CG\Billing\PricingSchemeAssignment\Storage\Api as PricingSchemeAssignmentApiStorage;
use CG\Billing\PricingSchemeAssignment\StorageInterface as PricingSchemeAssignmentStorage;
use CG\Billing\Subscription\Package\Storage\Api as SubscriptionPackageApiStorage;
use CG\Billing\Subscription\Package\StorageInterface as SubscriptionPackageStorage;
use CG\Clearbooks\Invoice\InvoiceInitialisationService;
use CG\Clearbooks\Payment\AllocationService as ClearbooksPaymentAllocationService;
use CG\Payment\AllocationServiceInterface as PaymentAllocationService;
use CG\Payment\InvoiceInitialisationInterface;
use CG\Settings\Billing\Clearbooks\Customer\Storage\Api as ClearbooksCustomerApiStorage;
use CG\Settings\Billing\Clearbooks\Customer\StorageInterface as ClearbooksCustomerStorage;
use CG\Settings\Contact\Storage\Api as ContactApiStorage;
use CG\Settings\Contact\StorageInterface as ContactStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                PricingSchemeAssignmentStorage::class => PricingSchemeAssignmentApiStorage::class,
                SubscriptionPackageStorage::class => SubscriptionPackageApiStorage::class,
                PackageStorage::class => PackageApiStorage::class,
                InvoiceInitialisationInterface::class => InvoiceInitialisationService::class,
                ClearbooksCustomerStorage::class => ClearbooksCustomerApiStorage::class,
                ContactStorage::class => ContactApiStorage::class,
                PaymentAllocationService::class => ClearbooksPaymentAllocationService::class,
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
            ClearbooksCustomerApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ],
            ],
            ContactApiStorage::class => [
                'parameters' => [
                    'client' => 'billing_guzzle',
                ],
            ],
        ],
    ],
];