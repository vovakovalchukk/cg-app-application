<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Carrier\Entity;
use CG\Channel\Shipping\Provider\Carrier\Collection;
use CG\Channel\Shipping\Provider\Carrier\Mapper;
use CG\Channel\Shipping\Provider\ChannelsInterface;
use CG\Channel\Shipping\Provider\Channels\ShippingOptionsInterface;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\Order\Shared\ShippableInterface as Order;
use CG\User\OrganisationUnit\Service as UserOuService;

class Service implements ChannelsInterface, ShippingOptionsInterface
{
    const FEATURE_FLAG_SHIPSTATION = 'ShipStation';
    const FEATURE_FLAG_USPS = 'USPS';
    const FEATURE_FLAG_ROYAL_MAIL = 'ShipStation Royal Mail';
    const FEATURE_FLAG_FEDEX_UK = 'FedEx UK';
    const FEATURE_FLAG_DHL_EXPRESS_UK = 'DHL Express UK';

    /** @var Mapper */
    protected $mapper;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var UserOuService */
    protected $userOuService;
    /** @var Collection */
    protected $carriers;

    public function __construct(
        Mapper $mapper,
        FeatureFlagsService $featureFlagsService,
        UserOuService $userOuService,
        array $carriersConfig = [],
        array $defaultBookingOptions = []
    ) {
        $this->mapper = $mapper;
        $this->featureFlagsService = $featureFlagsService;
        $this->userOuService = $userOuService;
        $this->carriers = $this->mapper->collectionFromArray($carriersConfig, $defaultBookingOptions);
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
    public function isOrderSupported(Account $account, Order $order)
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

    /**
     * @return array ['{Label}' => ['channel' => '{channel-name}' => 'region' => '{region|blank}']]
     */
    public function getShippingChannelOptions()
    {
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        if (!$this->featureFlagsService->isActive(static::FEATURE_FLAG_SHIPSTATION, $rootOu)) {
            return [];
        }

        $options = [];
        foreach ($this->carriers as $carrier) {
            if ($carrier->getFeatureFlag() && !$this->featureFlagsService->isActive($carrier->getFeatureFlag(), $rootOu)) {
                continue;
            }
            $options[$carrier->getDisplayName()] = [
                'channel' => $carrier->getChannelName(),
                'region' => ''
            ];
        }
        return $options;
    }
}
