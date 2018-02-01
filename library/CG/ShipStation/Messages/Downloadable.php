<?php
namespace CG\ShipStation\Messages;

class Downloadable
{
    /** @var string */
    protected $href;
    /** @var ?string */
    protected $type;

    public function __construct(string $href, ?string $type = null)
    {
        $this->href = $href;
        $this->type = $type;
    }

    public static function build($decodedJson): Downloadable
    {
        return new static(
            $decodedJson->href,
            $decodedJson->type ?? null
        );
    }

    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * @return self
     */
    public function setHref(string $href)
    {
        $this->href = $href;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function __toString()
    {
        return $this->getHref();
    }
}