<?php

use CG\Courier\Geopost\Command\ImportGeogaz;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/**
 * @var Di $di
 */
return [
    'geopost:importGeogaz' => [
        'description' => 'Import Geogaz data file to update version',
        'arguments' => [
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $import = new ImportGeogaz();
            $import();
        },
    ],
];
