<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\ChannelsInterface as ShippingProviderChannelsInterface;
use CG\Channel\Shipping\Provider\Channels\ShippingOptionsInterface as ShippingProviderChannelOptionsInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\EmailClientAwareInterface;
use CG\CourierAdapter\EmailClientInterface;
use CG\CourierAdapter\Provider\Implementation\Collection;
use CG\CourierAdapter\Provider\Implementation\Entity;
use CG\CourierAdapter\Provider\Implementation\Mapper;
use CG\CourierAdapter\StorageAwareInterface;
use CG\CourierAdapter\StorageInterface;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Order\Shared\ShippableInterface as Order;
use CG\User\OrganisationUnit\Service as UserOuService;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface as PsrLoggerAwareInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Zend\Di\Di;

class Service implements
    ShippingProviderChannelsInterface,
    ShippingProviderChannelOptionsInterface,
    PsrLoggerAwareInterface,
    StorageAwareInterface,
    EmailClientAwareInterface
{
    /** @var Mapper */
    protected $mapper;
    /** @var Di */
    protected $di;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var UserOuService */
    protected $userOuService;

    /** @var Collection */
    protected $adapterImplementations;
    /** @var PsrLoggerInterface */
    protected $psrLogger;
    /** @var StorageInterface */
    protected $storage;
    /** @var EmailClientInterface */
    protected $emailClient;

    protected $adapterImplementationCourierInstances = [];

    /**
     * @param array $adapterImplementationsConfig [['channelName' => 'example', 'displayName' => 'Example', 'courierFactory' => function() { return new \ExampleImplementation\Courier(); }, 'featureFlag' => '(Optional) Feature flag name']]
     */
    public function __construct(
        Mapper $mapper,
        Di $di,
        FeatureFlagsService $featureFlagsService,
        UserOuService $userOuService,
        array $adapterImplementationsConfig = []
    ) {
        $this->mapper = $mapper;
        $this->di = $di;
        $this->featureFlagsService = $featureFlagsService;
        $this->userOuService = $userOuService;
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
            throw new InvalidArgumentException('Adapter with channel name "'.$channelName.'" not found');
        }
        $adapterImplementationsByChannelName->rewind();
        return $adapterImplementationsByChannelName->current();
    }

    /**
     * @return CourierInterface
     */
    public function getAdapterImplementationCourierInstanceForAccount(Account $account, $specificInterface = null)
    {
        $adapterImplementation = $this->getAdapterImplementationForAccount($account);
        return $this->getAdapterImplementationCourierInstance($adapterImplementation, $specificInterface);
    }

    public function getAdapterImplementationCourierInstanceForChannel($channelName, $specificInterface = null)
    {
        $adapterImplementation = $this->getAdapterImplementationByChannelName($channelName);
        return $this->getAdapterImplementationCourierInstance($adapterImplementation, $specificInterface);
    }

    /**
     * @return CourierInterface
     */
    public function getAdapterImplementationCourierInstance(Entity $adapterImplementation, $specificInterface = null)
    {
        if (isset($this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()])) {
            $courierInstance = $this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()];
            $this->checkCourierInstanceAgainstSpecificInterface(
                $adapterImplementation->getChannelName(), $courierInstance, $specificInterface
            );
            return $courierInstance;
        }

        $courierInstance = call_user_func($adapterImplementation->getCourierFactory(), $this->di);
        $this->checkCourierInstanceAgainstSpecificInterface(
            $adapterImplementation->getChannelName(), $courierInstance, $specificInterface
        );

        // Pass the logger along so implementers can do their own logging
        $courierInstance->setLogger($this->psrLogger);

        if ($courierInstance instanceof StorageAwareInterface) {
            $courierInstance->setStorage($this->storage);
        }
        if ($courierInstance instanceof EmailClientAwareInterface) {
            $courierInstance->setEmailClient($this->emailClient);
        }

        // Some couriers need to be told if we're in a non-Live environment
        if (defined('ENVIRONMENT') && ENVIRONMENT != 'live' && is_callable([$courierInstance, 'setTestMode'])) {
            $courierInstance->setTestMode(true);
        }

        $this->adapterImplementationCourierInstances[$adapterImplementation->getChannelName()] = $courierInstance;
        return $courierInstance;
    }

    protected function checkCourierInstanceAgainstSpecificInterface(
        $channelName,
        CourierInterface $courierInstance,
        $specificInterface = null
    ) {
        if ($specificInterface && !$courierInstance instanceof $specificInterface) {
            throw new InvalidArgumentException('Tried to get a courier instance for channel ' . $channelName . ' but its adapter does not implement ' . $specificInterface);
        }
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
    public function isOrderSupported(Account $account, Order $order)
    {
        return $this->isProvidedChannel($account->getChannel());
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
        $rootOU = $this->userOuService->getRootOuByActiveUser();
        $options = [];
        /** @var Entity $adapterImplementation */
        foreach ($this->adapterImplementations as $adapterImplementation) {
            if ($adapterImplementation->getFeatureFlag() &&
                !$this->featureFlagsService->isActive($adapterImplementation->getFeatureFlag(), $rootOU)
            ) {
                continue;
            }
            $options[$adapterImplementation->getDisplayName()] = [
                'channel' => $adapterImplementation->getChannelName(),
                'region' => ''
            ];
        }
        return $options;
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

    // For \CG\CourierAdapter\StorageAwareInterface
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    public function setEmailClient(EmailClientInterface $emailClient)
    {
        $this->emailClient = $emailClient;
        return $this;
    }
}
