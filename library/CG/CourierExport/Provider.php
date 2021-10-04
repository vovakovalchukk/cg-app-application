<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\Channel\Shipping\Provider\BookingOptions\CancelActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptions\CancelAllActionDescriptionInterface;
use CG\Channel\Shipping\Provider\Channels\ShippingOptionsInterface;
use CG\Channel\Shipping\Provider\ChannelsInterface;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierServiceProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\Channel\Shipping\Provider\Service\ExportInterface;
use CG\Channel\Shipping\Provider\ServiceInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

class Provider implements
    ChannelsInterface,
    ShippingOptionsInterface,
    BookingOptionsInterface,
    ServiceInterface,
    ExportInterface,
    CancelActionDescriptionInterface,
    CancelAllActionDescriptionInterface,
    CarrierServiceProviderCancelInterface
{
    /** @var Factory */
    protected $factory;
    /** @var ActiveUserInterface */
    protected $activeUser;

    protected $channels = [
        'royal-mail-click-drop' => 'Royal Mail Click & Drop',
    ];

    public function __construct(Factory $factory, ActiveUserInterface $activeUser)
    {
        $this->factory = $factory;
        $this->activeUser = $activeUser;
    }

    public function isOrderSupported(Account $account, Order $order)
    {
        return $this->isProvidedChannel($account->getChannel());
    }

    public function getProviderChannelNameForChannel($channelName)
    {
        return $this->channels[$channelName] ?? null;
    }

    public function getNamespaceNameForChannel($channelName)
    {
        return hyphenToFullyQualifiedClassname($channelName, 'CourierExport');
    }

    public function getProvidedChannels()
    {
        $channels = array_keys($this->channels);
        return array_combine($channels, $channels);
    }

    public function getShippingChannelOptions()
    {
        return array_map(
            function(string $channel) {
                return [
                    'channel' => $channel,
                    'region' => '',
                ];
            },
            array_flip($this->channels)
        );
    }

    public function connectAccount(string $channel, int $accountId = null): Account
    {
        $channelName = $this->channels[$channel] ?? null;
        if (!$channelName) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a provider channel', $channel));
        }

        return $this->factory->getCreationService($channel, $channelName)->connectAccount(
            $this->activeUser->getActiveUserRootOrganisationUnitId(),
            $accountId
        );
    }

    public function isProvidedAccount(Account $account)
    {
        return $this->isProvidedChannel($account->getChannel());
    }

    public function isProvidedChannel($channel)
    {
        return isset($this->channels[$channel]);
    }

    public function getCarrierBookingOptionsForAccount(Account $account, $serviceCode = null)
    {
        return $this->factory->getExportOptionsForAccount($account)->getDefaultExportOptions($serviceCode);
    }

    public function addCarrierSpecificDataToListArray(
        array $data,
        Account $account,
        OrganisationUnit $rootOu,
        OrderCollection $orders,
        ProductDetailCollection $productDetails
    ) {
        return $this->factory->getExportOptionsForAccount($account)->addCarrierSpecificDataToListArray($data);
    }

    public function getDataForCarrierBookingOption(
        $option,
        Order $order,
        Account $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ) {
        return $this->factory->getExportOptionsForAccount($account)->getDataForCarrierExportOption(
            $option,
            $order,
            $service,
            $rootOu,
            $productDetails
        );
    }

    public function getCarrierPackageTypesOptions(Account $account): array
    {
        // Not required but need to satisfy interface
        return [];
    }

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
        throw new \RuntimeException('CourierExport providers only support exporting labels');
    }

    public function isExportAllowedForOrder(Account $account, Order $order): bool
    {
        return $this->isOrderSupported($account, $order);
    }

    public function exportOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        User $user
    ): ExportDocumentInterface {
        return $this->factory->getExporterForAccount($shippingAccount)->exportOrders(
            $orders,
            $orderLabels,
            $ordersData,
            $orderParcelsData,
            $orderItemsData,
            $rootOu,
            $user
        );
    }

    public function getCancelActionDescription(Account $shippingAccount): string
    {
        return 'Cancel export';
    }

    public function getCancelAllActionDescription(Account $shippingAccount): string
    {
        return 'Cancel all exports';
    }

    public function isCancellationAllowedForOrder(Account $account, Order $order)
    {
        return true;
    }

    public function cancelOrderLabels(OrderLabelCollection $orderLabels, OrderCollection $orders, Account $shippingAccount)
    {
        // Not required but need to satisfy interface
    }
}