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
use CG\UkMail\Collection\Service as CollectionService;
use CG\Locale\CountryNameByAlpha3Code;
use CG\UkMail\DomesticConsignment\Service as DomesticConsignmentService;
use CG\UkMail\Shipment as UkMailShipment;
use CG\UkMail\DeliveryService as UkMailDeliveryService;
use CG\CourierAdapter\Address as CAAddress;
use CG\UkMail\Shipment\Package as UkMailPackage;
use CG\CourierAdapter\Provider\Implementation\Package\Content as CAContent;
use CG\UkMail\DeliveryProducts\Service as DeliveryProductsService;
use CG\UkMail\CustomsDeclaration\Service as CustomsDeclarationService;
use CG\UkMail\Consignment\International\Service as InternationalConsignmentService;

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

            print_r($caAccount);

            /** @var AuthenticateService $authenticateService */
            $authenticateService = $di->newInstance(AuthenticateService::class);

            $token = $authenticateService->getAuthenticationToken($caAccount);

            echo "TOKEN ".$token."\n";

            $collectionDate = (new \DateTime())->setDate(2021, 7, 23);

            /** @var CollectionService $collectionService */
            $collectionService = $di->newInstance(CollectionService::class);

            $collectionJobNumber = $collectionService->getCollectionJobNumber($caAccount, $token, $collectionDate, true);

            echo "COLLECTION JOB NUMBER ".$collectionJobNumber."\n";

            /** @var DomesticConsignmentService $domesticConsignmentService */
            $domesticConsignmentService = $di->newInstance(DomesticConsignmentService::class);

            $deliveryService = new UkMailDeliveryService(
                101,
                'Parcels Next Day - deliver to neighbour - signature',
                0
            );

            $deliveryAddress = new CAAddress(
                '',
                'Dominik',
                'Gajewski',
                '83 Bellott Street',
                '',
                '',
                'Manchester',
                'Greater Manchester',
                'M8 0AZ',
                'United Kingdom',
                'GB',
                'dominikgajewski1@gmail.com',
                '07874619071'
            );

            $deliveryAddressIntl = new CAAddress(
                '',
                'Dominik',
                'Gajewski',
                'Puzaka 2/35',
                '',
                '',
                'Krakow',
                '',
                '31-303',
                'Poland',
                'PL',
                'dominikgajewski1@gmail.com',
                '07874619071'
            );

            $deliveryAddressIntl2 = new CAAddress(
                '',
                'Dominik',
                'Gajewski',
                'Am Borsigturm 44',
                '',
                '',
                'Dinslaken',
                'Nordrhein-Westfalen',
                '46535',
                'Germany',
                'DE',
                'dominikgajewski1@gmail.com',
                '07874619071'
            );

            $contents[] = new CAContent(
                'Testing Chili Con Carne',
                '56081911',
                '',
                'GB', 1, 1.5, 10, 'GBP',
                'Testing Chili Con Carne',
                '',
                'cgiv-9628-chili-con-carne'
            );
            $packages[] = new UkMailPackage(1, 1.5, 0.22, 0.22, 0.22, $contents);

            $shipment = new UkMailShipment(
                $deliveryService,
                36,
                $caAccount,
                $deliveryAddressIntl2,
                null,
                null,
                $collectionDate,
                $packages,
                false,
                'GB1234567891',
                'IM1234567891',
                '36'
            );

//            $domesticConsignmentResponse = $domesticConsignmentService->requestDomesticConsignment($shipment, $token, $collectionJobNumber);
//
//            print_r($domesticConsignmentResponse);

            /** @var DeliveryProductsService $deliveryProductsService */
            $deliveryProductsService = $di->newInstance(DeliveryProductsService::class);

            echo "DeliveryProducts\n";

//            $deliveryProductsResponse = $deliveryProductsService->getDeliveryProducts($shipment);
//            print_r($deliveryProductsResponse);

            $deliveryProduct = $deliveryProductsService->checkIntlServiceAvailabilityForShipment($shipment);

//            /** @var CustomsDeclarationService $customsDeclarationService */
//            $customsDeclarationService = $di->newInstance(CustomsDeclarationService::class);
//
//            $customsDeclaration = $customsDeclarationService->getCustomsDeclaration($shipment, 'full');
//
//            print_r($customsDeclaration);

            print_r($deliveryProduct);
            if (!isset($deliveryProduct)) {
                echo "NO DELIVERY PRODUCT\n";
                return;
            }

            /** @var InternationalConsignmentService $internationalConsignmentService */
            $internationalConsignmentService = $di->newInstance(InternationalConsignmentService::class);

            $internationalConsignmentResponse = $internationalConsignmentService->requestInternationalConsignment(
                $shipment, $token, $collectionJobNumber, $deliveryProduct->getCustomsDeclaration()
            );

            print_r($internationalConsignmentResponse);





        },
        'arguments' => [
        ],
        'options' => [],
    ],


    'adhoc:testCountry' => [
        'description' => '',
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {

            $res = CountryNameByAlpha3Code::getCountryAlpha3CodeFromCountryAlpha2Code('');

            print_r($res);
            echo "\n";


        },
        'arguments' => [
        ],
        'options' => [],
    ]


];
