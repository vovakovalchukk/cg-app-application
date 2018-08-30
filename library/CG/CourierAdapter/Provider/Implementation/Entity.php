<?php
namespace CG\CourierAdapter\Provider\Implementation;

class Entity
{
    /** @var string */
    protected $channelName;
    /** @var string */
    protected $displayName;
    /** @var callable */
    protected $courierFactory;
    /** @var string|null */
    protected $featureFlag;

    public function __construct(
        string $channelName,
        string $displayName,
        callable $courierFactory,
        ?string $featureFlag = null
    ) {
        $this->setChannelName($channelName)
            ->setDisplayName($displayName)
            ->setCourierFactory($courierFactory)
            ->setFeatureFlag($featureFlag);
    }

    /**
     * @return string
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return callable
     */
    public function getCourierFactory()
    {
        return $this->courierFactory;
    }

    public function getFeatureFlag(): ?string
    {
        return $this->featureFlag;
    }

    // Required by Collection
    public function getId()
    {
        return $this->channelName;
    }

    /**
     * @return self
     */
    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * @return self
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return self
     */
    public function setCourierFactory(callable $courierFactory)
    {
        $this->courierFactory = $courierFactory;
        return $this;
    }

    public function setFeatureFlag(?string $featureFlag): Entity
    {
        $this->featureFlag = $featureFlag;
        return $this;
    }
}