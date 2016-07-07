<?php
namespace CG\CourierAdapter\Provider\Adapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Provider\Adapter\Collection;
use CG\CourierAdapter\Provider\Adapter\Entity;
use CG\CourierAdapter\Provider\Adapter\Mapper;

class Service
{
    /** @var Mapper */
    protected $mapper;
    /** @var Collection */
    protected $adapters;

    protected $adapterCourierInterfaces = [];

    /**
     * @param array $adaptersConfig [['channelName' => 'example', 'displayName' => 'Example', 'courierInterfaceClosure' => function() { return new \ExampleAdapter\Courier(); }]]
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

    public function getAdapterCourierInterfaceForAccount(Account $account)
    {
        $adapter = $this->getAdapterForAccount($account);
        return $this->getAdapterCourierInterface($adapter);
    }

    public function getAdapterCourierInterface(Entity $adapter)
    {
        if (isset($this->adapterCourierInterfaces[$adapter->getChannelName()])) {
            return $this->adapterCourierInterfaces[$adapter->getChannelName()];
        }
        $courerInterface = call_user_func($adapter->getCourierInterfaceClosure());
        $this->adapterCourierInterfaces[$adapter->getChannelName()] = $courerInterface;
        return $courerInterface;
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
