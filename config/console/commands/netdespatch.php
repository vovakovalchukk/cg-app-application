<?php

use CG\Account\Shared\Mapper as AccountMapper;
use CG\Account\Client\Storage\Api as AccountStorage;
use CG\Channel\Type as ChannelType;
use CG\NetDespatch\Account\CreationService as AccountCreationService;
use CG\NetDespatch\Account\Type as AccountType;
use CG\NetDespatch\Credentials;
use CG\Stdlib\DateTime as StdlibDateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/**
 * @var Di $di
 */
return [
    'netdespatch:createAccountWithCredentials' => [
        'description' => 'Create a new Account that already has its NetDespatch credentials (i.e. skip the credentials request step)',
        'arguments' => [
            'organisationUnitId' => [
                'description' => 'The ID of the OrganisationUnit to create the account against',
                'required' => true,
            ],
            'rmAccountId' => [
                'description' => 'The Royal Mail account ID for the new account',
                'required' => true,
            ],
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $mapper = $di->get(AccountMapper::class);
            $accountStorage = $di->get(AccountStorage::class);
            $cryptor = $di->get('netdespatch_cryptor');

            $rmAccountId = $input->getArgument('rmAccountId');
            $credentials = (new Credentials())->setRmAccountId($rmAccountId);
            $account = $mapper->fromArray([
                "channel" => AccountCreationService::CHANNEL_NAME,
                "organisationUnitId" => $input->getArgument('organisationUnitId'),
                "displayName" => "Royal Mail (OBA) ".$rmAccountId,
                "credentials" => $cryptor->encrypt($credentials),
                "active" => true,
                "deleted" => false,
                "expiryDate" => null,
                "type" => ChannelType::SHIPPING,
                "stockManagement" => false,
                "externalData" => [
                    'accountType' => AccountType::DOMESTIC,
                    'formSubmissionDate' => (new StdlibDateTime())->stdFormat()
                ]
            ]);

            $account = $accountStorage->save($account);

            $output->writeln(sprintf('Created Account %d for OU %d. Go to the details page in OrderHub and click \'Connect Account\' to complete the process', $account->getId(), $account->getOrganisationUnitId()));
        },
    ],
];
