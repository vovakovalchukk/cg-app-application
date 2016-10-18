<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierProviderServiceCancelInterface;
use CG\Channel\Shipping\Provider\ServiceInterface as CarrierProviderServiceInterface;
use CG\Channel\Shipping\Provider\Service\ManifestInterface as CarrierProviderServiceManifestInterface;
use CG\CourierAdapter\Manifest\GeneratingInterface as ManifestGeneratingInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Cancel as LabelCancelService;
use CG\CourierAdapter\Provider\Label\Create as LabelCreateService;
use CG\CourierAdapter\Provider\Manifest\Service as ManifestService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

class Service implements
    CarrierProviderServiceInterface,
    CarrierProviderServiceCancelInterface,
    CarrierProviderServiceManifestInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var LabelCreateService */
    protected $labelCreateService;
    /** @var LabelCancelService */
    protected $labelCancelService;
    /** @var ManifestService */
    protected $manifestService;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        LabelCreateService $labelCreateService,
        LabelCancelService $labelCancelService,
        ManifestService $manifestService
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setLabelCreateService($labelCreateService)
            ->setLabelCancelService($labelCancelService)
            ->setManifestService($manifestService);
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

    /**
     * @return bool
     */
    public function isCancellationAllowedForOrder(Account $account, Order $order)
    {
        return $this->labelCancelService->isCancellationAllowedForOrder($account, $order);
    }

    /**
     * @return null
     */
    public function cancelOrderLabels(OrderLabelCollection $orderLabels, OrderCollection $orders, Account $shippingAccount)
    {
        $this->labelCancelService->cancelOrderLabels($orderLabels, $shippingAccount);
    }

    /**
     * @return bool
     */
    public function isManifestingAllowedForAccount(Account $account)
    {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        return ($courierInstance instanceof ManifestGeneratingInterface);
    }

    /**
     * @return bool
     */
    public function isManifestingOnlyAllowedOncePerDayForAccount(Account $account)
    {
        return false;
    }

    /**
     * @return null
     */
    public function createManifestForAccount(Account $shippingAccount, AccountManifest $accountManifest)
    {
        $this->manifestService->createManifestForAccount($shippingAccount, $accountManifest);
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

    protected function setLabelCancelService(LabelCancelService $labelCancelService)
    {
        $this->labelCancelService = $labelCancelService;
        return $this;
    }

    protected function setManifestService(ManifestService $manifestService)
    {
        $this->manifestService = $manifestService;
        return $this;
    }
}