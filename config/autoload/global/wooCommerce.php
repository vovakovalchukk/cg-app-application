<?php
use CG\Account\Shared\Entity as Account;
use CG\WooCommerce\Client\Factory as ClientFactory;
use CG\WooCommerce\Service\Categories\OrderHub as OrderHubCategoriesService;
use CG\WooCommerce\Service\Repository as ServiceRepository;

return [
    'di' => [
        'definition' => [
            'class' => [
                ServiceRepository::class => [
                    'methods' => [
                        'registerService' => [
                            'application' => ['required' => true],
                            'service' => ['required' => true],
                        ],
                    ],
                ],
            ]
        ],
        'instance' => [
            ClientFactory::class => [
                'parameters' => [
                    'cryptor' => 'woocommerce_cryptor',
                    'guzzle' => function() { return 'woocommerce_guzzle'; },
                ]
            ],
            ServiceRepository::class => [
                'shared' => true,
                'injections' => [
                    'registerService' => [
                        ['application' => Account::APPLICATION_OH, 'service' => OrderHubCategoriesService::class]
                    ],
                ],
            ]
        ]
    ]
];
