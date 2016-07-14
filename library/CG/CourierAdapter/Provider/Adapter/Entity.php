<?php
namespace CG\CourierAdapter\Provider\Adapter;

class Entity
{
    /** @var string */
    protected $channelName;
    /** @var string */
    protected $displayName;
    /** @var callable */
    protected $courierFactory;

    public function __construct(
        $channelName,
        $displayName,
        $courierFactory
    ) {
        $this->setChannelName($channelName)
            ->setDisplayName($displayName)
            ->setCourierFactory($courierFactory);
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
}