<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\ServiceInterface as ShippingProviderServiceInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG\User\Entity as User;

class Service implements ShippingProviderServiceInterface
{
    /** @var CarrierService */
    protected $carrierServive;
    /** @var AccountService */
    protected $accountService;
    /** @var Creator */
    protected $labelCreator;

    public function __construct(
        CarrierService $carrierServive,
        AccountService $accountService,
        Creator $labelCreator
    ) {
        $this->carrierServive = $carrierServive;
        $this->accountService = $accountService;
        $this->labelCreator = $labelCreator;
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
     * @param OrderCollection $orders The orders to operate on
     * @param OrderLabelCollection $orderLabels Pre-created OrderLabels to save label PDF data to
     * @param array $ordersData Additional data for each Order:
     *         ['{orderId}' => ['signature' => bool, 'deliveryInstructions' => string, ...]]
     * @param array $orderParcelsData Additional data for each parcel:
     *         ['{orderId}' => ['{parcelIndex}' => ['value' => float, 'height' => float, ...]]]
     * @param array $orderItemsData Additional data for each item:
     *         ['{orderId}' => ['{itemId}' => ['weight' => float, 'hstariff' => string, ...]]]
     * @param OrganisationUnit $rootOu
     * @param Account $shippingAccount
     * @param User $user The user who triggered the request. Required if creating Order\Trackings
     * @return array ['{orderId}' => bool || CG\Stdlib\Exception\Runtime\ValidationMessagesException]
     *          for each order whether a label was successfully created or a ValidationMessagesException if it errored
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
            $orderItemsData,
            $rootOu,
            $shippingAccount,
            $user,
            $shipStationAccount
        );
    }

    protected function getShipStationAccountForShippingAccount(Account $shippingAccount): Account
    {
        $shipStationAccountId = $shippingAccount->getExternalDataByKey('shipstationAccountId');
        return $this->accountService->fetch($shipStationAccountId);
    }
}