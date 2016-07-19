<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\Account\Shared\Entity as Account;
use CG\Channel\ShippingOptionsProviderInterface;
use CG\CourierAdapter\Provider\Implementation\Collection;
use CG\CourierAdapter\Provider\Implementation\Entity;
use CG\CourierAdapter\Provider\Implementation\Mapper;
use CG\Order\Shared\Entity as Order;
use Psr\Log\LoggerAwareInterface as PsrLoggerAwareInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class Service implements ShippingOptionsProviderInterface, PsrLoggerAwareInterface
{
    /** @var Mapper */
    protected $mapper;
    /** @var Collection */
    protected $adapterImplementations;
    /** @var PsrLoggerInterface */
    protected $psrLogger;

    protected $adapterImplementationCourierInstances = [];

    /**
     * @param array $adapterImplementationsConfig [['channelName' => 'example', 'displayName' => 'Example', 'courierFactory' => function() { return new \ExampleImplementation\Courier(); }]]
     */
    public function __construct(Mapper $mapper, array $adapterImplementationsConfig = [])
    {
        $this->setMapper($mapper);
        $this->setAdapterImplementations(new Collection(Entity::class, __CLASS__));
        foreach ($adapterImplementationsConfig as $adapterImplementationConfig) {
            $this->adapterImplementations->attach($this->mapper->fromArray($adapterImplementationConfig));
        }
    }

    /**
     * @return Entity
     */
    public function getAdapterImplementationForAccount(Account $account)
    {
        return $this->getAdapterImplementationByChannelName($account->getChannel());
    }

    /**
     * @return Entity
     */
    public function getAdapterImplementationByChannelName($channelName)
    {
        $adapterImplementationsByChannelName = $this->adapterImplementations->getBy('channelName', $channelName);
        if (count($adapterImplementationsByChannelName) == 0) {
            throw new \InvalidArgumentException('Adapter with channel name "'.$channelName.'" not found');
        }
        $adapterImplementationsByChannelName->rewind();
        return $adapterImplementationsByChannelName->current();
    }

    /**
     * @return \CG\CourierAdapter\CourierInterface
     */
    public function getAdapterImplementationCourierInstanceForAccount(Account $account)
    {
        $adapterImplementation = $this->getAdapterImplementationForAccount($account);
        return $this->getAdapterImplementationCourierInstance($adapterImplementation);
    }

    /**
     * @return \CG\CourierAdapter\CourierInterface
     */
    public function getAdapterImplementationCourierInstance(Entity $adapterImplementation)
    {
        if (isset($this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()])) {
            return $this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()];
        }
        $courierInstance = call_user_func($adapterImplementation->getCourierFactory());
        // Pass the logger along so implementers can do their own logging
        $courierInstance->setLogger($this->psrLogger);

        $this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()] = $courierInstance;
        return $courierInstance;
    }

    /**
     * @return bool
     */
    public function isProvidedChannel($channelName)
    {
        try {
            $this->getAdapterImplementationByChannelName($channelName);
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
        foreach ($this->adapterImplementations as $adapterImplementation) {
            $channels[$adapterImplementation->getChannelName()] = $adapterImplementation->getChannelName();
        }
        return $channels;
    }

    /**
     * @return array
     */
    public function getShippingChannelOptions()
    {
        $options = [];
        foreach ($this->adapterImplementations as $adapterImplementation) {
            $options[$adapterImplementation->getDisplayName()] = [
                'channel' => $adapterImplementation->getChannelName(),
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

    protected function setAdapterImplementations(Collection $adapterImplementations)
    {
        $this->adapterImplementations = $adapterImplementations;
        return $this;
    }

    // For \Psr\Log\LoggerAwareInterface
    public function setLogger(PsrLoggerInterface $psrLogger)
    {
        $this->psrLogger = $psrLogger;
        $this->psrLogger->setCGLogCode('CourierAdapter');
        return $this;
    }
}
