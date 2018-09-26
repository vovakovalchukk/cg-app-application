<?php

use CG\Command\NullActiveUser;
use CG\Di\Di;
use CG\ShipStation\Command\Api as ApiCommand;
use CG\ShipStation\Command\UpdateShippingServices;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\ActiveUserInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @var Di $di */
return [
    'shipstation:api' => [
        'description' => 'Run arbitrary queries against the ShipStation api',
        'arguments' => [
            'accountId' => [
                'description' => 'The account to send the request to. This should be the shipstation account, not the courier account.',
                'required' => true,
            ],
            'endpoint' => [
                'description' => 'The endpoint to send request to',
                'required' => true,
            ],
            'payload' => [
                'description' => '(Optional) Payload to send as part of request'
            ],
        ],
        'options' => [
            'method' => [
                'description' => 'Specifies the request method to use e.g. GET, POST',
                'value' => true,
                'required' => true,
                'shortcut' => 'X',
            ]
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            $di->instanceManager()->setTypePreference(ActiveUserInterface::class, [new NullActiveUser()]);
            /** @var ApiCommand $command */
            $command = $di->get(ApiCommand::class);

            try {
                $response = $command(
                    $input->getArgument('accountId'),
                    $input->getArgument('endpoint'),
                    $input->getArgument('payload'),
                    $input->getOption('method')
                );
                $output->writeln($response->getJsonResponse());
            } catch (StorageException $e) {
                echo (string) $e->getPrevious()->getResponse();
            }
        }
    ],
    'shipstation:updateShippingServices' => [
        'description' => 'Fetch the latest shipping services from ShipStation for an Account',
        'arguments' => [
            'accountId' => [
                'description' => 'The account to update. This should be the courier account, not the shipstation account.',
                'required' => true,
            ],
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $di->instanceManager()->setTypePreference(ActiveUserInterface::class, [new NullActiveUser()]);
            $command = $di->get(UpdateShippingServices::class);
            $accountId = $input->getArgument('accountId');

            $output->writeln('Starting update of shipping services for Account ' . $accountId);
            $command($accountId);
            $output->writeln('Finished update of shipping services for Account ' . $accountId . '. See logs for details.');
        }
    ]
];
