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
use CG\Stdlib\DateTime as StdlibDateTime;

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


    'adhoc:showSubscriptions' => [
        'description' => '',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var \CG\Billing\Subscription\Service $subService */
            $subService = $di->newInstance(CG\Billing\Subscription\Service::class);

            $now = new DateTime();

            $filter = new \CG\Billing\Subscription\Filter();
            $filter->setLimit('all')
                ->setPage(1)
                ->setStartedOnOrBeforeDate('2018-01-01 00:00:00') //$now->format(StdlibDateTime::FORMAT)
                ->setEndedOnOrAfterDate('2018-01-01 05:00:00') //$now->format(StdlibDateTime::FORMAT)
                ->setOuId([66]);

            $subscriptions = $subService->fetchCollectionByFilter($filter);

            print_r($subscriptions);

            if ($subscriptions->count() <= 1) {
                return true;
            }
return true;
            /* @var $subscription \CG\Billing\Subscription\Entity */
            foreach ($subscriptions as $subscription) {
                /* @var $packages \CG\Billing\Package\Collection */
                $packages = $subscription->getPackages();

                /* @var $package \CG\Billing\Package\Entity */
                $package = $packages->getFirst();



                if ($package->getName() == 'Trial') {
                    continue;
                }

                echo $package->getName()."\n";

//                print_r($packages);
            }


        },
        'arguments' => [],
        'options' => [],
    ]



];
