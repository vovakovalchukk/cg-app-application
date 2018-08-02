<?php
use CG\Account\Client\Storage\Api as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Di\Di;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Carrier\AccountDeciderInterface;
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
            $di->instanceManager()->setTypePreference('CG\User\ActiveUserInterface', [new class implements CG\User\ActiveUserInterface {
                public function getActiveUser()
                {
                    // TODO: Implement getActiveUser() method.
                }

                public function setActiveUser(Entity $activeUser)
                {
                    // TODO: Implement setActiveUser() method.
                }

                public function getActiveUserRootOrganisationUnitId()
                {
                    // TODO: Implement getActiveUserRootOrganisationUnitId() method.
                }

                public function isAdmin()
                {
                    // TODO: Implement isAdmin() method.
                }

                public function getCompanyId()
                {
                    // TODO: Implement getCompanyId() method.
                }

                public function getLocale(): string
                {
                    // TODO: Implement getLocale() method.
                }

                public function setLocale(string $locale)
                {
                    // TODO: Implement setLocale() method.
                }

                public function getTimezone(): string
                {
                    // TODO: Implement getTimezone() method.
                }

                public function setTimezone(string $timezone)
                {
                    // TODO: Implement setTimezone() method.
                }
            }]);
            /** @var Client $client */
            $client = $di->get(Client::class);
            /** @var AccountService $accountService */
            $accountService = $di->get(AccountService::class);
            /** @var Account $account */
            $account = $accountService->fetch($input->getArgument('accountId'));
            // If we've been passed a courier Account convert it to the ShipStation Account
            if ($account->getChannel() != 'shipstationAccount') {
                $accountDeciderFactory = $di->get(AccountDeciderFactory::class);
                /** @var AccountDeciderInterface $accountDecider */
                $accountDecider = $accountDeciderFactory($account->getChannel());
                $account = $accountDecider->getShipStationAccountForRequests($account);
            }

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
