<?php
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Di\Di;
use CG\ShipStation\Client;
use CG\ShipStation\Command\Request as ApiRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @var Di $di */
return [
    'shipstation:api' => [
        'description' => 'Run arbitrary queries against the ShipStation api',
        'arguments' => [
            'accountId' => [
                'description' => 'The account to send the request to',
                'required' => true,
            ],
            'endpoint' => [
                'description' => 'The endpoint to send request to',
                'required' => true,
            ],
            'payload' => [
                'description' => 'Payload to send as part of request'
            ],
        ],
        'options' => [
            'request' => [
                'description' => 'Specifies the request method to use',
                'value' => true,
                'required' => true,
                'shortcut' => 'X',
            ]
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var Client $client */
            $client = $di->get(Client::class);
            /** @var AccountService $accountService */
            $accountService = $di->get(AccountService::class);
            /** @var Account $account */
            $account = $accountService->fetch($input->getArgument('accountId'));

            $request = new ApiRequest(
                $input->getArgument('endpoint'),
                $input->getOption('request'),
                $input->getArgument('payload')
            );

            $response = $client->sendRequest($request, $account);
            $output->write($response->getJsonResponse());
        }
    ]
];
