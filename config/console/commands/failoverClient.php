<?php
use CG\Http\Guzzle\Http\FailoverClient\Prioritiser\SymfonyConsole as Prioritiser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/**
 * @var Di $di
 */
return [
    'failoverClient:prioritise' => [
        'description' => 'Re-prioritise the failover client server list',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
                /**
                 * @var Prioritiser $prioritiser
                 */
                $prioritiser = $di->get(Prioritiser::class);
                $prioritiser($input, $output);
            },
        'arguments' => [
            Prioritiser::ARGUMENT_HOSTS => [
                'description' => 'List of hosts to be re-prioritised, if not set all hosts will be re-prioritised',
                'required' => false,
                'array' => true,
            ],
        ],
    ],
];
