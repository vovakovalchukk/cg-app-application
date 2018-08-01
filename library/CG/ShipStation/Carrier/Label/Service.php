<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\Channel\Shipping\Provider\Service\CancelInterface as ShippingProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\FetchRatesInterface as ShippingProviderFetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\ManifestInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Channel\Shipping\Provider\ServiceInterface as ShippingProviderServiceInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Carrier\Label\Creator\Factory as LabelCreatorFactory;
use CG\ShipStation\Carrier\Rates\Service as RatesService;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\User\Entity as User;

class Service implements ShippingProviderServiceInterface, ShippingProviderCancelInterface, ShippingProviderFetchRatesInterface, ManifestInterface
{
    /** @var CarrierService */
    protected $carrierServive;
    /** @var ShipStationService */
    protected $shipStationService;
    /** @var LabelCreatorFactory */
    protected $labelCreatorFactory;
    /** @var Canceller */
    protected $labelCanceller;
    /** @var RatesService */
    protected $ratesService;
    /** @var AccountDeciderFactory */
    protected $accountDeciderFactory;

    protected $carrierRateSupport = [
        'usps-ss' => true,
    ];

    public function __construct(
        CarrierService $carrierServive,
        ShipStationService $shipStationService,
        LabelCreatorFactory $labelCreatorFactory,
        Canceller $labelCanceller,
        RatesService $ratesService,
        AccountDeciderFactory $accountDeciderFactory
    ) {
        $this->carrierServive = $carrierServive;
        $this->shipStationService = $shipStationService;
        $this->labelCreatorFactory = $labelCreatorFactory;
        $this->labelCanceller = $labelCanceller;
        $this->ratesService = $ratesService;
        $this->accountDeciderFactory = $accountDeciderFactory;
    }

    /**
     * @return bool Is the given Account one that is managed by this Provider?
     */
    public function isProvidedAccount(Account $account)
    {
        return $this->carrierServive->isProvidedAccount($account);
    }

    /**
     * @return bool Is the given channel one that is managed by this Provider?
     */
    public function isProvidedChannel($channel)
    {
        return $this->carrierServive->isProvidedChannel($channel);
    }

    /**
     * @inheritdoc
     */
    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        User $user
    ) {
        // If / when TECH-92 is done we'll be passed these objects instead of the arrays and so wont need these lines any more
        $ordersData = OrderDataCollection::fromArray($ordersData);
        $orderParcelsData = OrderParcelsDataCollection::fromArray($orderParcelsData);

        /** @var AccountDeciderInterface $accountDecider */
        $accountDecider = ($this->accountDeciderFactory)($shippingAccount->getChannel());
        $shipStationAccountToUse = $accountDecider->getShipStationAccountForRequests($shippingAccount);
        $shippingAccountToUse = $accountDecider->getShippingAccountForRequests($shippingAccount);

        $labelCreator = ($this->labelCreatorFactory)($shippingAccount->getChannel());
        return $labelCreator->createLabelsForOrders(
            $orders,
            $orderLabels,
            $ordersData,
            $orderParcelsData,
            $rootOu,
            $shippingAccountToUse,
            $shipStationAccountToUse
        );
    }

    public function isCancellationAllowedForOrder(Account $account, Order $order)
    {
        return $this->carrierServive->getCarrierForAccount($account)->isCancellationAllowed();
    }

    public function cancelOrderLabels(
        OrderLabelCollection $orderLabels,
        OrderCollection $orders,
        Account $shippingAccount
    ) {
        /** @var AccountDeciderInterface $accountDecider */
        $accountDecider = ($this->accountDeciderFactory)($shippingAccount->getChannel());
        $shipStationAccountToUse = $accountDecider->getShipStationAccountForRequests($shippingAccount);
        $shippingAccountToUse = $accountDecider->getShippingAccountForRequests($shippingAccount);

        $this->labelCanceller->cancelOrderLabels($orderLabels, $orders, $shippingAccountToUse, $shipStationAccountToUse);
    }

    public function isFetchRatesAllowedForOrder(Account $shippingAccount, Order $order): bool
    {
        return $this->carrierRateSupport[$shippingAccount->getChannel()] ?? false;
    }

    public function fetchRatesForOrders(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount
    ): ShippingRateCollection {
        /** @var AccountDeciderInterface $accountDecider */
        $accountDecider = ($this->accountDeciderFactory)($shippingAccount->getChannel());
        $shipStationAccountToUse = $accountDecider->getShipStationAccountForRequests($shippingAccount);
        $shippingAccountToUse = $accountDecider->getShippingAccountForRequests($shippingAccount);

        return $this->ratesService->fetchRatesForOrders(
            $orders,
            $ordersData,
            $ordersParcelsData,
            $ordersItemsData,
            $rootOu,
            $shippingAccountToUse,
            $shipStationAccountToUse
        );
    }

    /**
     * @return bool
     */
    public function isManifestingAllowedForAccount(Account $account)
    {
        // TODO: Implement isManifestingAllowedForAccount() method.
    }

    /**
     * @return bool
     */
    public function isManifestingOnlyAllowedOncePerDayForAccount(Account $account)
    {
        // TODO: Implement isManifestingOnlyAllowedOncePerDayForAccount() method.
    }

    /**
     * Generate the manifest with the courier then call setExternalId() with the courier's ID for the manifest
     * and setManifest() with the base64 encoded PDF data of the manifest itself.
     * @param Account $shippingAccount
     * @param AccountManifest $accountManifest
     * @throws \CG\Stdlib\Exception\Storage if there is a problem generating the manifest
     */
    public function createManifestForAccount(Account $shippingAccount, AccountManifest $accountManifest)
    {
        // TODO: Implement createManifestForAccount() method.
    }
}