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

use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;

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
    'label:create' => [
        'description' => 'HELLO',
        'arguments' => [
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use ($di)
        {
            /* @var $createLabel \CG\CourierAdapter\Provider\Label\Create */
            $createLabel = $di->newInstance(CG\CourierAdapter\Provider\Label\Create::class);

            /* @var $orderService \CG\Order\Service\Service */
            $orderService = $di->newInstance(CG\Order\Service\Service::class);

            /* @var $organisationUnitService \CG\OrganisationUnit\Service */
            $organisationUnitService = $di->newInstance(CG\OrganisationUnit\Service::class);

            /* @var $accountService \CG\Account\Client\Service */
            $accountService = $di->newInstance(CG\Account\Client\Service::class);

            /* @var $userService \CG\User\Service */
            $userService = $di->newInstance(CG\User\Service::class);

            $filter = (new OrderFilter())->setId('22-10');

            $orders = $orderService->fetchCollectionByFilter($filter);

            $orderLabels = new OrderLabelCollection(OrderLabel::class, '');

            $ordersData = [];
            $orderParcelsData = [];
            $orderItemsData = [];

            /* @var $order Order */
            foreach ($orders as $order) {
                $orderLabel = new OrderLabel($order->getOrganisationUnitId(), $order->getId(), '', date('Y-m-d H:i:s'));
                $orderLabels->attach($orderLabel);

                $ordersData[$order->getId()] = [
                    'service' => 880,
                    'packageType' => 'Parcel',
                    'addOn' => '',
                    'deliveryInstructions' => ''
                ];

                $itemParcelAssignment = [];
                /* @var $orderItem OrderItem */
                foreach ($order->getItems() as $orderItem) {
                    $itemParcelAssignment[$orderItem->getId()] = $orderItem->getItemQuantity();

                    $orderItemData[$orderItem->getId()] = [
                        'weight' => 10 * $orderItem->getItemQuantity()
                    ];
                }

                $orderParcelsData[$order->getId()] = [
                    'weight' => 5,
                    'itemParcelAssignment' => $itemParcelAssignment
                ];

                $orderItemsData[$order->getId()] = $orderItemData;
            }

            $organisationUnit = $organisationUnitService->fetch(2);

            $shippingAccount = $accountService->fetch(17);

            $user = $userService->fetch(2);

            $createLabel->createLabelsForOrders(
                $orders,
                $orderLabels,
                $ordersData,
                $orderParcelsData,
                $orderItemsData,
                $organisationUnit,
                $shippingAccount,
                $user
            );


        },
    ],
];
