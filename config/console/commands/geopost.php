<?php

use CG\Courier\Geopost\Command\ImportGeogaz;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

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
];
