<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\Channel\Shipping\Provider\ChannelsInterface;
use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\Channel\Shipping\Provider\Service\ExportInterface;
use CG\Channel\Shipping\Provider\ServiceInterface;
use CG\CourierExport\RoyalMailClickDrop\GenericAccountProvider as RoyalMailClickDrop;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\User\Entity as User;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

class Provider implements ChannelsInterface, BookingOptionsInterface, ServiceInterface, ExportInterface
{
    /** @var Factory */
    protected $factory;

    protected $channels = [
        RoyalMailClickDrop::CHANNEL => 'Royal Mail Click & Drop',
    ];

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function isOrderSupported($channelName, Order $order)
    {
        return $this->isProvidedChannel($channelName);
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

    public function addCarrierSpecificDataToListArray(array $data, Account $account)
    {
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
        return $this->isOrderSupported($account->getChannel(), $order);
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
}