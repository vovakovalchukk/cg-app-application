<?php

use CG\Command\NullActiveUser;
use CG\Courier\Geopost\Command\ImportGeogaz;
use CG\Courier\Geopost\Command\UpdateConsignmentAndParcelRange;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
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
    'geopost:updateConsignmentAndParcelRange' => [
        'description' => 'Update consignment and parcel number ranges for Geopost accounts',
        'arguments' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            $di->instanceManager()->setTypePreference('CG\User\ActiveUserInterface', [new NullActiveUser()]);
            /** @var UpdateConsignmentAndParcelRange $command */
            $command = $di->get(UpdateConsignmentAndParcelRange::class, ['input' => $input, 'output' => $output, 'cryptor' => 'courieradapter_cryptor']);
            $command();
        }
    ],
];
