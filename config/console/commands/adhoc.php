<?php
use CG\Account\Shared\Entity as Account;
use CG\CourierExport\Factory;
use CG\Di\Di;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use CG\User\UserInterface;
use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\UkMail\Request\Rest\Authenticate;
use CG\UkMail\Request\Rest\Collection as CollectionRequest;
use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Authenticate\Service as AuthenticateService;

/** @var Di $di */
return [
    'adhoc:migrateRoyalMailClickAndDrop' => [
        'description' => 'Creates a new RoyalMail Click & Drop account for any ou that has previously used the hard code version',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var OrderLabelService $orderLabelService */
            $orderLabelService = $di->newInstance(OrderLabelService::class);
            /** @var Factory $factory */
            $factory = $di->newInstance(Factory::class);

            try {
                $orderLabels = $orderLabelService->fetchCollectionByFilter(
                    (new OrderLabelFilter('all', 1))
                        ->setShippingAccountId([2147483647])
                        ->setOrganisationUnitId($input->getArgument('organisationUnitId') ?? [])
                );
            } catch (NotFound $exception) {
                $output->writeln('<fg=yellow>No OrderLabels found to migrate</>');
                return;
            }

            $output->writeln(sprintf('<fg=green>Found %d OrderLabel%s to migrate</>', $orderLabels->count(), $orderLabels->count() == 1 ? '' : 's'));
            foreach ($orderLabels->getArrayOf('organisationUnitId') as $organisationUnitId) {
                $di->instanceManager()->addSharedInstance(
                    new class($organisationUnitId) implements ActiveUserInterface
                    {
                        /** @var int */
                        protected $organisationUnitId;
                        /** @var ?User */
                        protected $activeUser;

                        public function __construct(int $organisationUnitId)
                        {
                            $this->organisationUnitId = $organisationUnitId;
                        }

                        public function getActiveUser()
                        {
                            return $this->activeUser;
                        }

                        public function setActiveUser(User $activeUser)
                        {
                            $this->activeUser = $activeUser;
                            return $this;
                        }

                        public function getActiveUserRootOrganisationUnitId()
                        {
                            return $this->getCompanyId();
                        }

                        public function isAdmin()
                        {
                            return false;
                        }

                        public function getCompanyId()
                        {
                            return $this->getCompanyId();
                        }
                    },
                    ActiveUserInterface::class
                );

                /** @var Account $account */
                $account = $factory
                    ->getCreationService('royal-mail-click-drop', 'Royal Mail Click & Drop')
                    ->connectAccount($organisationUnitId);

                $output->writeln(sprintf('Created account %d for ou %d, migrating OrderLabels...', $account->getId(), $organisationUnitId));
                $ouOrderLabels = $orderLabels->getBy('organisationUnitId', $organisationUnitId);
                $progress = new ProgressBar($output, $ouOrderLabels->count());

                /** @var OrderLabel $ouOrderLabel */
                foreach ($ouOrderLabels as $ouOrderLabel) {
                    $orderLabelService->save($ouOrderLabel->setShippingAccountId($account->getId()));
                    $progress->advance();
                }

                $output->writeln('');
            }
        },
        'arguments' => [
            'organisationUnitId' => [
                'description' => 'Limit the ous to the specified [default: all]',
                'required' => false,
                'array' => true,
            ],
        ],
        'options' => [],
    ],


    'adhoc:testUkMail' => [
        'description' => '',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {

            $organisationUnitId = 2;
            $di->instanceManager()->addSharedInstance(
                new class($organisationUnitId) implements ActiveUserInterface
                {
                    /** @var int */
                    protected $organisationUnitId;
                    /** @var ?User */
                    protected $activeUser;

                    public function __construct(int $organisationUnitId)
                    {
                        $this->organisationUnitId = $organisationUnitId;
                    }

                    public function getActiveUser()
                    {
                        return $this->activeUser;
                    }

                    public function setActiveUser(UserInterface $activeUser)
                    {
                        $this->activeUser = $activeUser;
                        return $this;
                    }

                    public function getActiveUserRootOrganisationUnitId()
                    {
                        return $this->getCompanyId();
                    }

                    public function isAdmin()
                    {
                        return false;
                    }

                    public function getCompanyId()
                    {
                        return $this->getCompanyId();
                    }

                    public function getLocale(): string
                    {
                        return 'gb_UK';
                    }

                    public function setLocale(string $locale)
                    {
                        // TODO: Implement setLocale() method.
                    }

                    public function getTimezone(): string
                    {
                        return 'Europe/London';
                    }

                    public function setTimezone(string $timezone)
                    {
                        // TODO: Implement setTimezone() method.
                    }
                },
                ActiveUserInterface::class
            );

            /** @var \CG\Account\Client\Service $accountService */
            $accountService = $di->newInstance(AccountService::class);

            $account = $accountService->fetch(20);

//            print_r($account);

            /** @var \CG\UkMail\Client\Factory $clientFactory */
            $clientFactory = $di->newInstance(CG\UkMail\Client\Factory::class);
            /** @var CAAccountMapper $caAccountMapper */
            $caAccountMapper = $di->get(CAAccountMapper::class);

            $caAccount = $caAccountMapper->fromOHAccount($account);

//            print_r($caAccount);

//            $authRequest = new Authenticate(
//                $caAccount->getCredentials()['apiKey'],
//                $caAccount->getCredentials()['username'],
//                $caAccount->getCredentials()['password']
//            );
//
//            $client = $clientFactory($caAccount, $authRequest);
//
//            $resposne = $client->sendRequest($authRequest);
//
//            print_r($resposne);

            /** @var AuthenticateService $authenticateService */
            $authenticateService = $di->newInstance(AuthenticateService::class);

            $token = $authenticateService->getAuthenticationToken($caAccount);

            echo "TOKEN ".$token."\n";

            $collectionRequest = new CollectionRequest(
                $caAccount->getCredentials()['apiKey'],
                $caAccount->getCredentials()['username'],
                $token,
                $collectionDate,
                $closedForLunch,
                $earliestTime,
                $latestTime,
                $specialInstructions
            );

            $client = $clientFactory($caAccount, $collectionRequest);

            $resposne = $client->sendRequest($collectionRequest);

            print_r($resposne);

        },
        'arguments' => [
        ],
        'options' => [],
    ]

];
