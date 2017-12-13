<?php
namespace CG\ShipStation\EntityTrait;

use CG\Stdlib\DateTime;

trait TimestampableTrait
{
    /** @var  DateTime */
    protected $createdAt;
    /** @var  DateTime */
    protected $modifiedAt;

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
