<?php
namespace CG\ShipStation\Messages;

use CG\Stdlib\DateTime;

class Timestamp
{
    /** @var  DateTime */
    protected $createdAt;
    /** @var  DateTime */
    protected $modifiedAt;

    public function __construct(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getModifiedAt(): ?DateTime
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }
}
