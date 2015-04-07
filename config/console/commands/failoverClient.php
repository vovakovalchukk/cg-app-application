<?php
use CG\Http\Guzzle\Http\FailoverClient\StatusChecker\SymfonyConsole as StatusChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/**
 * @var Di $di
 */
return [
    'failoverClient:checkStatus' => [
        'description' => 'Check the status of the failover client server list',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
                /**
                 * @var StatusChecker $statusChecker
                 */
                $statusChecker = $di->get(StatusChecker::class);
                $statusChecker($input, $output);
            },
        'arguments' => [
            StatusChecker::ARGUMENT_HOSTS => [
                'description' => 'List of hosts to be checked, if not set all hosts will be checked',
                'required' => false,
                'array' => true,
            ],
        ],
    ],
];
