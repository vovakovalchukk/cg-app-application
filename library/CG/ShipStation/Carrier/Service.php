<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\ChannelsInterface;
use CG\Order\Shared\ShippableInterface as Order;

class Service implements ChannelsInterface
{
    /** @var Mapper */
    protected $mapper;
    /** @var Collection */
    protected $carriers;

    public function __construct(Mapper $mapper, array $carriersConfig = [])
    {
        $this->mapper = $mapper;
        $this->carriers = $this->mapper->collectionFromArray($carriersConfig);
    }

    public function getCarrierForAccount(Account $account): Entity
    {
        return $this->getCarrierByChannelName($account->getChannel());
    }

    public function getCarrierByChannelName(string $channelName): Entity
    {
        $carriersByChannelName = $this->carriers->getBy('channelName', $channelName);
        if (count($carriersByChannelName) == 0) {
            throw new \InvalidArgumentException('Carrier with channel name "'.$channelName.'" not found');
        }
        return $carriersByChannelName->getFirst();
    }

    /**
     * @return bool Does this provider support the provided order for the selected channel?
     */
    public function isOrderSupported($channelName, Order $order)
    {
        return true;
    }

    /**
     * @return string Get the 'channel' name to use for this provider itself (usually lowercased provider name)
     */
    public function getProviderChannelNameForChannel($channelName)
    {
        return 'shipstation';
    }

    /**
     * @return string Get the namespace name to use for this provider (usually ProperCased provider name)
     */
    public function getNamespaceNameForChannel($channelName)
    {
        return 'ShipStation';
    }

    /**
     * @return array ['{channelName}' => '{channelName}'], channelName used as both key and value for convenience
     */
    public function getProvidedChannels()
    {
        $channels = [];
        foreach ($this->carriers as $carrier) {
            $channels[$carrier->getChannelName()] = $carrier->getChannelName();
        }
        return $channels;
    }

    /**
     * @return bool Is the given Account one that is managed by this Provider?
     */
    public function isProvidedAccount(Account $account)
    {
        return $this->isProvidedChannel($account->getChannel());
    }

    /**
     * @return bool Is the given channel one that is managed by this Provider?
     */
    public function isProvidedChannel($channel)
    {
        $channels = $this->getProvidedChannels();
        return isset($channels[$channel]);
    }
}