<?php
use CG\Account\Client\Storage\Api as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Command\NullActiveUser;
use CG\Di\Di;
use CG\ShipStation\Client;
use CG\ShipStation\Command\Request as ApiRequest;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\Entity;
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
            'request' => [
                'description' => 'Specifies the request method to use e.g. GET, POST',
                'value' => true,
                'required' => true,
                'shortcut' => 'X',
            ]
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            $di->instanceManager()->setTypePreference('CG\User\ActiveUserInterface', [new NullActiveUser()]);
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

            try {
                $response = $client->sendRequest($request, $account);
                $output->writeln($response->getJsonResponse());
            } catch (StorageException $e) {
                echo (string) $e->getPrevious()->getResponse();
            }
        }
    ]
];
