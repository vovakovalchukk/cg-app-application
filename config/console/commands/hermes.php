<?php

use CG\Command\MockActiveUser;
use CG\Hermes\Command\Api as ApiCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'hermes:api' => [
        'description' => 'Run arbitrary queries against the Hermes API',
        'arguments' => [
            'accountId' => [
                'description' => 'The account to send the request to',
                'required' => true,
            ],
            'endpoint' => [
                'description' => 'The endpoint to send request to',
                'required' => true,
            ],
            'body' => [
                'description' => 'The body of the request to send'
            ],
        ],
        'options' => [
            'method' => [
                'description' => 'Specifies the request method to use',
                'value' => true,
                'required' => false,
                'shortcut' => 'X',
            ]
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $accountId = $input->getArgument('accountId');
            $endpoint = $input->getArgument('endpoint');
            $body = $input->getArgument('body');
            $method = $input->getOption('method') ?? 'POST';

            $di->instanceManager()->setTypePreference('CG\User\ActiveUserInterface', [new MockActiveUser()]);
            /** @var ApiCommand $command */
            $command = $di->get(ApiCommand::class);
            $output->write($command($accountId, $method, $endpoint, $body));
        }
    ]
];