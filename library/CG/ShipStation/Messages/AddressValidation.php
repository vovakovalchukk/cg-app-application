<?php
namespace CG\ShipStation\Messages;

class AddressValidation
{
    /** @var $status */
    protected $status;
    /** @var ShipmentAddress */
    protected $originalAddress;
    /** @var ShipmentAddress */
    protected $matchedAddress;
    /** @var array */
    protected $messages;

    public function __construct(
        $status,
        ShipmentAddress $originalAddress,
        ShipmentAddress $matchedAddress,
        \stdClass ...$messages
    ) {
        $this->status = $status;
        $this->originalAddress = $originalAddress;
        $this->matchedAddress = $matchedAddress;
        $this->messages = $messages;
    }

    public static function build($decodedJson): AddressValidation
    {
        $originalAddress = ShipmentAddress::build($decodedJson->original_address);
        $matchedAddress = ShipmentAddress::build($decodedJson->matched_address);
        return new static(
            $decodedJson->status,
            $originalAddress,
            $matchedAddress,
            ...$decodedJson->messages
        );
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getOriginalAddress(): ShipmentAddress
    {
        return $this->originalAddress;
    }

    /**
     * @return self
     */
    public function setOriginalAddress(ShipmentAddress $originalAddress)
    {
        $this->originalAddress = $originalAddress;
        return $this;
    }

    public function getMatchedAddress(): ShipmentAddress
    {
        return $this->matchedAddress;
    }

    /**
     * @return self
     */
    public function setMatchedAddress(ShipmentAddress $matchedAddress)
    {
        $this->matchedAddress = $matchedAddress;
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return self
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }
}