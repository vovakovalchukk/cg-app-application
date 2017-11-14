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
use CG\Channel\Type;
//use \DateTime;

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
    'accounts:test' => [
        'description' => '',
        'arguments' => [
//            'organisationUnitId' => [
//                'description' => 'The ID of the OrganisationUnit to create the account against',
//                'required' => true,
//            ],
//            'rmAccountId' => [
//                'description' => 'The Royal Mail account ID for the new account',
//                'required' => true,
//            ],
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            $type = 'sales';
            $limit = 100;
            $page = 1;
            $urlPlugin = 'settings/channel/';

            $dm = $di->newInstance(CG\Account\DataTableMapper::class);
            $accountService = $di->newInstance(CG\Account\Client\Service::class);

            try {
                $accounts = $accountService->fetchByOUAndStatus(
                    [2],
                    null,
                    false,
                    $limit,
                    $page,
                    $type
                );

                // Hack to hide amazon shipping accounts
                if ($type == Type::SHIPPING) {
                    $amazonAccounts = new SplObjectStorage();

                    /** @var AccountEntity $account */
                    foreach ($accounts as $account) {
                        if ($account->getChannel() == 'amazon') {
                            $amazonAccounts->attach($account);
                        }
                    }

                    $accounts->removeAll($amazonAccounts);
                }

                $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = (int) $accounts->getTotal();

                foreach ($accounts as $account) {
                    $now = null;
                    $dm->toDataTableArray($account);

                    if (!($now instanceof DateTime)) {
                        $now = new DateTime();
                    }

                    $links = [
                        'manage' => ChannelController::ROUTE_CHANNELS . '/' . ChannelController::ROUTE_ACCOUNT
                    ];

                    $manageLinks = [];
                    foreach ($links as $class => $link) {
                        $route = Module::ROUTE . '/' . ChannelController::ROUTE . '/' . $link;
                        $routeMap = explode('/', $route);
                        $manageLinks[] = [
                            'name' => end($routeMap),
                            'class' => $class,
                            'href' => $urlPlugin->fromRoute($route, ['account' => $account->getId(), 'type' => $type])
                        ];
                    }

                    $dataTableArray['manageLinks'] = $manageLinks;



                    $dataTableArray['organisationUnit'] = $this->getOrganisationUnitCompanyName($account->getOrganisationUnitId());
                    $dataTableArray['status'] = $account->getStatus($now);
                    // Don't allow users to enable pending OBA accounts, we enable them once we get the credentials
                    if ($account->getChannel() == NetDespatchAccountCreationService::CHANNEL_NAME
                        && !$this->activeUser->isAdmin()
                        && $account->getPending()
                    ) {
                        $dataTableArray['disabled'] = true;
                    }

                    $dataTableArray['expiryDate'] = 'N/A';
                    $expiryDate = $account->getExpiryDateAsDateTime();
                    if ($expiryDate instanceof DateTime) {
                        $timeToExpiry = $expiryDate->getTimestamp() - $now->getTimestamp();
                        $dataTableArray['expiryDate'] = ($timeToExpiry > 0) ? $expiryDate->format('jS F Y') : 'Expired';
                    }

                    print_r($dataTableArray);


//                    $data['Records'][] = $this->getMapper()->toDataTableArray($account, $this->url(), $type);
                }
            } catch (NotFound $exception) {
                // No accounts so ignoring
            }





//            $cc = $di->newInstance(Settings\Controller\ChannelController::class);



//            $mapper = $di->get(AccountMapper::class);
//            $accountStorage = $di->get(AccountStorage::class);
//            $cryptor = $di->get('netdespatch_cryptor');
//
//            $rmAccountId = $input->getArgument('rmAccountId');
//            $credentials = (new Credentials())->setRmAccountId($rmAccountId);
//            $account = $mapper->fromArray([
//                "channel" => AccountCreationService::CHANNEL_NAME,
//                "organisationUnitId" => $input->getArgument('organisationUnitId'),
//                "displayName" => "Royal Mail (OBA) ".$rmAccountId,
//                "credentials" => $cryptor->encrypt($credentials),
//                "active" => true,
//                "deleted" => false,
//                "expiryDate" => null,
//                "type" => ChannelType::SHIPPING,
//                "stockManagement" => false,
//                "externalData" => [
//                    'accountType' => AccountType::DOMESTIC,
//                    'formSubmissionDate' => (new StdlibDateTime())->stdFormat()
//                ]
//            ]);
//
//            $account = $accountStorage->save($account);
//
//            $output->writeln(sprintf('Created Account %d for OU %d. Go to the details page in OrderHub and click \'Connect Account\' to complete the process', $account->getId(), $account->getOrganisationUnitId()));
        },
    ],
];
