<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\Channel\CarrierProviderServiceInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Create as LabelCreateService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

class Service implements CarrierProviderServiceInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var LabelCreateService */
    protected $labelCreateService;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        LabelCreateService $labelCreateService
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setLabelCreateService($labelCreateService);
    }

    /**
     * @return array ['{orderId}' => bool || \CG\Stdlib\Exception\Runtime\ValidationMessagesException]
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
        return $this->labelCreateService->createLabelsForOrders(
            $orders, $orderLabels, $ordersData, $orderParcelsData, $orderItemsData, $rootOu, $shippingAccount, $user
        );
    }

    public function cancelOrderLabels(OrderLabelCollection $orderLabels, Account $shippingAccount)
    {
        // TODO
    }

    /**
     * @return bool
     */
    public function isProvidedAccount(Account $account)
    {
        return $this->adapterImplementationService->isProvidedAccount($account);
    }

    /**
     * @return bool
     */
    public function isProvidedChannel($channel)
    {
        return $this->adapterImplementationService->isProvidedChannel($channelName);
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setLabelCreateService(LabelCreateService $labelCreateService)
    {
        $this->labelCreateService = $labelCreateService;
        return $this;
    }
}