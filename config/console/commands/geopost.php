<?php

use CG\Courier\Geopost\Command\ImportGeogaz;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;
use Orders\Courier\SpecificsAjax;
use CG\CourierExport\RoyalMailClickDrop\ExportOptions;

/**
 * @var Di $di
 */
return [
    'geopost:importGeogaz' => [
        'description' => 'Import Geogaz data file to update version',
        'arguments' => [
        ],
        'command' => function()
        {
            $import = new ImportGeogaz();
            $import();
        },
    ],


    'clickdroptest' => [
        'description' => 'Import Geogaz data file to update version',
        'arguments' => [
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $spec = $di->get(ExportOptions::class);


            $data = [
                [
                    'actionRow' => false,
                    'service' => 'Royal Mail 48',
                    'addOns' => '',
                    'requiredFields' => [
                        'packageType' => [],
                        'addOns' => [],
                    ]
                ],
                [
//                    'actionRow' => true,
                    'service' => '',//'Royal Mail 48',
                    'requiredFields' => [
                        'packageType' => [],
                        'addOns' => [],
                    ],
                    'addOns' => '',
                ],
                [
                    'actionRow' => true,
                    'service' => '',//'Royal Mail 48',
                    'requiredFields' => [
                        'packageType' => [],
                        'addOns' => [],
                    ],
                    'addOns' => '',
                ],
            ];


            $res = $spec->addCarrierSpecificDataToListArray($data);


            print_r($res);
        },
    ],
];
