<?php

use CG\UkMail\Authenticate\Service as AuthenticateService;
use CG\UkMail\DeliveryService\Service as DeliveryServiceService;

return [
    'di' => [
        'instance' => [
            AuthenticateService::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis'
                ],
            ],
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
                                'displayName' => 'Parcels 48Hr - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '72',
                                'displayName' => 'Parcels 48Hr + - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '97',
                                'displayName' => 'Pallets 24Hr - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '98',
                                'displayName' => 'Pallets 48Hr - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '101',
                                'displayName' => 'Worldwide Air - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '102',
                                'displayName' => 'DHL Parcel International - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '204',
                                'displayName' => 'International Road Economy - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '206',
                                'displayName' => 'DHL Parcel Connect - deliver to doorstep only - signature',
                                'domestic' => false,
                            ],
                            [
                                'serviceCode' => '40',
                                'displayName' => 'Bagit Small 1kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '240',
                                'displayName' => 'Bagit Small 1kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '230',
                                'displayName' => 'Bagit Small 1kg Next Day - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '41',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '241',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '231',
                                'displayName' => 'Bagit Small 1kg Next Day 12:00 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '49',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '242',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '232',
                                'displayName' => 'Bagit Small 1kg Next Day 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '42',
                                'displayName' => 'Bagit Small 1kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '43',
                                'displayName' => 'Bagit Small 1kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '245',
                                'displayName' => 'Bagit Small 1kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '235',
                                'displayName' => 'Bagit Small 1kg Saturday - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '46',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '246',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '236',
                                'displayName' => 'Bagit Small 1kg Saturday 10:30 - leave safe - non signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '44',
                                'displayName' => 'Bagit Small 1kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '30',
                                'displayName' => 'Bagit Medium 2kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '250',
                                'displayName' => 'Bagit Medium 2kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '31',
                                'displayName' => 'Bagit Medium 2kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '251',
                                'displayName' => 'Bagit Medium 2kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '39',
                                'displayName' => 'Bagit Medium 2kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '252',
                                'displayName' => 'Bagit Medium 2kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '32',
                                'displayName' => 'Bagit Medium 2kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '33',
                                'displayName' => 'Bagit Medium 2kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '255',
                                'displayName' => 'Bagit Medium 2kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '36',
                                'displayName' => 'Bagit Medium 2kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '256',
                                'displayName' => 'Bagit Medium 2kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '34',
                                'displayName' => 'Bagit Medium 2kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '20',
                                'displayName' => 'Bagit Large 5kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '260',
                                'displayName' => 'Bagit Large 5kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '21',
                                'displayName' => 'Bagit Large 5kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '261',
                                'displayName' => 'Bagit Large 5kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '29',
                                'displayName' => 'Bagit Large 5kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '262',
                                'displayName' => 'Bagit Large 5kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '22',
                                'displayName' => 'Bagit Large 5kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '23',
                                'displayName' => 'Bagit Large 5kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '265',
                                'displayName' => 'Bagit Large 5kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '26',
                                'displayName' => 'Bagit Large 5kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '266',
                                'displayName' => 'Bagit Large 5kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '24',
                                'displayName' => 'Bagit Large 5kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '10',
                                'displayName' => 'Bagit XL 10kg Next Day - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '270',
                                'displayName' => 'Bagit XL 10kg Next Day - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '11',
                                'displayName' => 'Bagit XL 10kg Next Day 12:00 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '271',
                                'displayName' => 'Bagit XL 10kg Next Day 12:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '19',
                                'displayName' => 'Bagit XL 10kg Next Day 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '272',
                                'displayName' => 'Bagit XL 10kg Next Day 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '12',
                                'displayName' => 'Bagit XL 10kg Next Day 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],

                            [
                                'serviceCode' => '13',
                                'displayName' => 'Bagit XL 10kg Saturday - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '275',
                                'displayName' => 'Bagit XL 10kg Saturday - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '16',
                                'displayName' => 'Bagit XL 10kg Saturday 10:30 - deliver to neighbour - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '276',
                                'displayName' => 'Bagit XL 10kg Saturday 10:30 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                            [
                                'serviceCode' => '14',
                                'displayName' => 'Bagit XL 10kg Saturday 09:00 - deliver to doorstep only - signature',
                                'domestic' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];