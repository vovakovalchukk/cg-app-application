<?php
namespace CG\CourierAdapter\Provider\Adapter;

class Entity
{
    /** @var string */
    protected $channelName;
    /** @var string */
    protected $displayName;
    /** @var callable */
    protected $courierInterfaceClosure;

    public function __construct(
        $channelName,
        $displayName,
        $courierInterfaceClosure
    ) {
        $this->setChannelName($channelName)
            ->setDisplayName($displayName)
            ->setCourierInterfaceClosure($courierInterfaceClosure);
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
    public function getCourierInterfaceClosure()
    {
        return $this->courierInterfaceClosure;
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
    public function setCourierInterfaceClosure(callable $courierInterfaceClosure)
    {
        $this->courierInterfaceClosure = $courierInterfaceClosure;
        return $this;
    }
}