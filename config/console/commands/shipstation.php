<?php
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Di\Di;
use CG\ShipStation\Client;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\ResponseAbstract;
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
            $client = $di->newInstance(Client::class, ['partnerApiKey' => '']);

            /** @var AccountService $accountService */
            $accountService = $di->newInstance(AccountService::class);

            /** @var Account $account */
            $account = $accountService->fetch($input->getArgument('accountId'));

            $request = new class ($input) extends RequestAbstract {
                private $input;

                public function __construct(InputInterface $input)
                {
                    $this->input = $input;
                }

                public function getUri(): string
                {
                    return $this->input->getArgument('endpoint');
                }

                public function toArray(): array
                {
                    $payload = $this->input->getArgument('payload') ?? '';
                    return json_decode($payload,1) ?? [];
                }

                public function getMethod(): string
                {
                    return $this->input->getOption('request');
                }

                public function getResponseClass(): string
                {
                    $responseClass = new class extends ResponseAbstract {
                        protected $json;

                        function build($decodedJson)
                        {
                            $this->json = $decodedJson;
                            return $this;
                        }
                    };
                    return get_class($responseClass);
                }
            };

            $response = $client->sendRequest($request, $account);
            $output->write($response->getJsonResponse());
        }
    ]
];
