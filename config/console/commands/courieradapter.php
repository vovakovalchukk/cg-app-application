<?php

use CG\CourierAdapter\Command\AppendShipstationRoyalMailDisplayNames;
use CG\CourierAdapter\Command\RetrieveConnectionRequestDetails;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Command\NullActiveUser;
use Zend\Di\Di;

/** @var Di $di */
return [
    'courieradapter:retrieveConnectionRequestDetails' => [
        'description' => 'Retrieves the details we store after a user submits a connection request form.',
        'arguments' => [
            'ouId' => [
                'description' => 'the ID of the organisation unit the account belongs to',
                'required' => true
            ],
            'shippingAccountId' => [
                'description' => 'the ID of the shipping account as it appears on the account management screen',
                'required' => true
            ]
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            /** @var RetrieveConnectionRequestDetails $command */
            $command = $di->get(RetrieveConnectionRequestDetails::class);
            $command(
                $input->getArgument('ouId'),
                $input->getArgument('shippingAccountId'),
                $output
            );
        },
    ],
    'courieradapter:appendAllShipstationRoyalMailAccountDisplayNames' => [
        'description' => 'Appends "- Shipstation" to all existing shipstation royal mail accounts',
        'arguments' => [],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            /** @var AppendShipstationRoyalMailDisplayNames $command */
            $di->instanceManager()->setTypePreference('CG\User\ActiveUserInterface', [new NullActiveUser()]);
            $command = $di->get(AppendShipstationRoyalMailDisplayNames::class);
            $command($output);
        },
    ],
];
