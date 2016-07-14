<?php
namespace CG\CourierAdapter\Provider\Adapter;

use CG\Account\Shared\Entity as Account;
use CG\Channel\ShippingOptionsProviderInterface;
use CG\CourierAdapter\Provider\Adapter\Collection;
use CG\CourierAdapter\Provider\Adapter\Entity;
use CG\CourierAdapter\Provider\Adapter\Mapper;
use CG\Order\Shared\Entity as Order;

class Service implements ShippingOptionsProviderInterface
{
    /** @var Mapper */
    protected $mapper;
    /** @var Collection */
    protected $adapters;

    protected $adapterCourierInterfaces = [];

    /**
     * @param array $adaptersConfig [['channelName' => 'example', 'displayName' => 'Example', 'courierFactory' => function() { return new \ExampleAdapter\Courier(); }]]
     */
    public function __construct(Mapper $mapper, array $adaptersConfig = [])
    {
        $this->setMapper($mapper);
        $this->setAdapters(new Collection(Entity::class, __CLASS__));
        foreach ($adaptersConfig as $adapterConfig) {
            $this->adapters->attach($this->mapper->fromArray($adapterConfig));
        }
    }

    /**
     * @return Entity
     */
    public function getAdapterForAccount(Account $account)
    {
        return $this->getAdapterByChannelName($account->getChannel());
    }

    /**
     * @return Entity
     */
    public function getAdapterByChannelName($channelName)
    {
        $adaptersByChannelName = $this->adapters->getBy('channelName', $channelName);
        if (count($adaptersByChannelName) == 0) {
            throw new \InvalidArgumentException('Adapter with channel name "'.$channelName.'" not found');
        }
        $adaptersByChannelName->rewind();
        return $adaptersByChannelName->current();
    }

    /**
     * @return \CG\CourierAdapter\CourierInterface
     */
    public function getAdapterCourierInterfaceForAccount(Account $account)
    {
        $adapter = $this->getAdapterForAccount($account);
        return $this->getAdapterCourierInterface($adapter);
    }

    /**
     * @return \CG\CourierAdapter\CourierInterface
     */
    public function getAdapterCourierInterface(Entity $adapter)
    {
        if (isset($this->adapterCourierInterfaces[$adapter->getChannelName()])) {
            return $this->adapterCourierInterfaces[$adapter->getChannelName()];
        }
        $courerInterface = call_user_func($adapter->getCourierFactory());
        $this->adapterCourierInterfaces[$adapter->getChannelName()] = $courerInterface;
        return $courerInterface;
    }

    /**
     * @return bool
     */
    public function isProvidedChannel($channelName)
    {
        try {
            $this->getAdapterByChannelName($channelName);
            return true;
        } catch (\InvalidArgumentException $ex) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isProvidedAccount(Account $account)
    {
        return $this->isProvidedChannel($account->getChannel());
    }

    /**
     * @return bool
     */
    public function isOrderSupported($channelName, Order $order)
    {
        return $this->isProvidedChannel($channelName);
    }

    /**
     * @return string
     */
    public function getProviderChannelNameForChannel($channelName)
    {
        return 'courieradapter';
    }

    /**
     * @return string
     */
    public function getNamespaceNameForChannel($channelName)
    {
        return 'CourierAdapter\Provider';
    }

    /**
     * @return array
     */
    public function getProvidedChannels()
    {
        $channels = [];
        foreach ($this->adapters as $adapter) {
            $channels[$adapter->getChannelName()] = $adapter->getChannelName();
        }
        return $channels;
    }

    /**
     * @return array
     */
    public function getShippingChannelOptions()
    {
        $options = [];
        foreach ($this->adapters as $adapter) {
            $options[$adapter->getDisplayName()] = [
                'channel' => $adapter->getChannelName(),
                'region' => ''
            ];
        }
        return $options;
    }

    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function setAdapters(Collection $adapters)
    {
        $this->adapters = $adapters;
        return $this;
    }
}
