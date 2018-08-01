<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\CancelInterface as ShippingProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\FetchRatesInterface as ShippingProviderFetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Channel\Shipping\Provider\ServiceInterface as ShippingProviderServiceInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\Rates\Service as RatesService;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG\User\Entity as User;

class Service implements ShippingProviderServiceInterface, ShippingProviderCancelInterface, ShippingProviderFetchRatesInterface
{
    /** @var CarrierService */
    protected $carrierServive;
    /** @var AccountService */
    protected $accountService;
    /** @var Creator */
    protected $labelCreator;
    /** @var Canceller */
    protected $labelCanceller;
    /** @var RatesService */
    protected $ratesService;

    protected $carrierRateSupport = [
        'usps-ss' => true,
    ];

    public function __construct(
        CarrierService $carrierServive,
        AccountService $accountService,
        Creator $labelCreator,
        Canceller $labelCanceller,
        RatesService $ratesService
    ) {
        $this->carrierServive = $carrierServive;
        $this->accountService = $accountService;
        $this->labelCreator = $labelCreator;
        $this->labelCanceller = $labelCanceller;
        $this->ratesService = $ratesService;
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
        $shipStationAccount = $this->getShipStationAccountForShippingAccount($shippingAccount);
        return $this->labelCreator->createLabelsForOrders(
            $orders,
            $orderLabels,
            $ordersData,
            $orderParcelsData,
            $rootOu,
            $shippingAccount,
            $shipStationAccount
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
        $shipStationAccount = $this->getShipStationAccountForShippingAccount($shippingAccount);
        $this->labelCanceller->cancelOrderLabels($orderLabels, $orders, $shippingAccount, $shipStationAccount);
    }

    protected function getShipStationAccountForShippingAccount(Account $shippingAccount): Account
    {
        $shipStationAccountId = $shippingAccount->getExternalDataByKey('shipstationAccountId');
        return $this->accountService->fetch($shipStationAccountId);
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
        return $this->ratesService->fetchRatesForOrders(
            $orders,
            $ordersData,
            $ordersParcelsData,
            $ordersItemsData,
            $rootOu,
            $shippingAccount
        );
    }
}