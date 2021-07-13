<?php

use CG\UkMail\DeliveryService\Service as DeliveryServiceService;

return [
    'di' => [
        'instance' => [
            DeliveryServiceService::class => [
                'parameters' => [
                    'servicesConfig' => [
                        'services' => [
                            [
                                'serviceCode' => '1',
                                'displayName' => 'Parcels Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '220',
                                'displayName' => 'Parcels Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '210',
                                'displayName' => 'Parcels Next Day - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '2',
                                'displayName' => 'Parcels Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '221',
                                'displayName' => 'Parcels Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '211',
                                'displayName' => 'Parcels Next Day 12:00 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '9',
                                'displayName' => 'Parcels Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '222',
                                'displayName' => 'Parcels Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '212',
                                'displayName' => 'Parcels Next Day 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '3',
                                'displayName' => 'Parcels Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '4',
                                'displayName' => 'Parcels Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '225',
                                'displayName' => 'Parcels Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '215',
                                'displayName' => 'Parcels Saturday - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '7',
                                'displayName' => 'Parcels Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '226',
                                'displayName' => 'Parcels Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '216',
                                'displayName' => 'Parcels Saturday 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '5',
                                'displayName' => 'Parcels Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '48',
                                'displayName' => 'Parcels 48 Hr - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '72',
                                'displayName' => 'Parcels 48 Hr - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '72',
                                'displayName' => 'Parcels 48 Hr - leave safe - non signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '',
                                'displayName' => '',
                                'domestic' => true,
                            ],



                        ]
                    ]
                ],
            ],
        ],
    ],
];