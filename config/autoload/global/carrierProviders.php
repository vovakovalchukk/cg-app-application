<?php

use CG\Channel\Shipping\Provider\BookingOptionsInterface as CarrierProviderBookingOptionsInterface;
use CG\Channel\Shipping\Provider\BookingOptions\Repository as CarrierProviderBookingOptionsRepository;
use CG\Channel\Shipping\Provider\ChannelsInterface as CarrierProviderChannelsInterface;
use CG\Channel\Shipping\Provider\Channels\Repository as CarrierProviderChannelsRepository;
use CG\Channel\Shipping\Provider\ServiceInterface as CarrierProviderServiceInterface;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;

// NetDespatch
use CG\NetDespatch\ShippingOptionsProvider as NetDespatchShippingOptionsProvider;
use CG\NetDespatch\Order\Service as NetDespatchOrderService;

// Amazon Logistics
use CG\Amazon\Carrier\ShippingChannelsProvider as AmazonShippingChannelsProvider;
use CG\Amazon\Carrier\CarrierProviderService as AmazonCarrierProvider;

// Amazon MCF (Multi-Channel Fulfilment)
use CG\Amazon\Mcf\ShippingChannelsProvider as AmazonMcfShippingChannelsProvider;
use CG\Amazon\Mcf\CarrierBookingOptions as AmazonMcfCarrierBookingOptions;
use CG\Amazon\Mcf\CarrierProviderService as AmazonMcfCarrierProviderService;

// CourierAdapters
use CG\CourierAdapter\Provider\Implementation\CarrierBookingOptions as CourierAdapterProviderCarrierBookingOptions;
use CG\CourierAdapter\Provider\Implementation\Service as CourierAdapterProviderImplementationService;
use CG\CourierAdapter\Provider\Label\Service as CourierAdapterProviderLabelService;

return [
    'di' => [
        'instance' => [
            CarrierProviderChannelsRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => NetDespatchShippingOptionsProvider::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfShippingChannelsProvider::class],
                        ['provider' => AmazonShippingChannelsProvider::class],
                        ['provider' => CourierAdapterProviderImplementationService::class],
                    ]
                ]
            ],
            CarrierProviderBookingOptionsRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => NetDespatchShippingOptionsProvider::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfCarrierBookingOptions::class],
                        ['provider' => AmazonShippingChannelsProvider::class],
                        ['provider' => CourierAdapterProviderCarrierBookingOptions::class],
                    ]
                ]
            ],
            CarrierProviderServiceRepository::class => [
                'injections' => [
                    'addProvider' => [
                        ['provider' => NetDespatchOrderService::class],
                        // Amazon MCF must come before Amazon Logistics
                        ['provider' => AmazonMcfCarrierProviderService::class],
                        ['provider' => AmazonCarrierProvider::class],
                        ['provider' => CourierAdapterProviderLabelService::class],
                    ]
                ]
            ],
        ],
        'definition' => [
            'class' => [
                CarrierProviderChannelsRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => CarrierProviderChannelsInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
                CarrierProviderBookingOptionsRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => CarrierProviderBookingOptionsInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
                CarrierProviderServiceRepository::class => [
                    'methods' => [
                        'addProvider' => [
                            'provider' => [
                                'type' => CarrierProviderServiceInterface::class,
                                'required' => true
                            ]
                        ]
                    ]
                ],
            ]
        ],
    ]
];