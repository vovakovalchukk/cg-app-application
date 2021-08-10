<?php
namespace CG\Courier\Geopost\Command\UpdateConsignmentAndParcelRange;

class Changes
{
    /** @var ?int */
    protected $originalParcelNumberStart = null;
    /** @var ?int */
    protected $originalParcelNumberEnd = null;
    /** @var ?int */
    protected $newParcelNumberStart = null;
    /** @var ?int */
    protected $newParcelNumberEnd = null;
    /** @var ?int */
    protected $originalConsignmentNumberStart = null;
    /** @var ?int */
    protected $originalConsignmentNumberEnd = null;
    /** @var ?int */
    protected $newConsignmentNumberStart = null;
    /** @var ?int */
    protected $newConsignmentNumberEnd = null;

    public function getOriginalParcelNumberStart(): ?int
    {
        return $this->originalParcelNumberStart;
    }

    public function setOriginalParcelNumberStart(?int $originalParcelNumberStart): self
    {
        $this->originalParcelNumberStart = $originalParcelNumberStart;
        return $this;
    }

    public function getOriginalParcelNumberEnd(): ?int
    {
        return $this->originalParcelNumberEnd;
    }

    public function setOriginalParcelNumberEnd(?int $originalParcelNumberEnd): self
    {
        $this->originalParcelNumberEnd = $originalParcelNumberEnd;
        return $this;
    }

    public function getNewParcelNumberStart(): ?int
    {
        return $this->newParcelNumberStart;
    }

    public function setNewParcelNumberStart(?int $newParcelNumberStart): self
    {
        $this->newParcelNumberStart = $newParcelNumberStart;
        return $this;
    }

    public function getNewParcelNumberEnd(): ?int
    {
        return $this->newParcelNumberEnd;
    }

    public function setNewParcelNumberEnd(?int $newParcelNumberEnd): self
    {
        $this->newParcelNumberEnd = $newParcelNumberEnd;
        return $this;
    }

    public function getOriginalConsignmentNumberStart(): ?int
    {
        return $this->originalConsignmentNumberStart;
    }

    public function setOriginalConsignmentNumberStart(?int $originalConsignmentNumberStart): self
    {
        $this->originalConsignmentNumberStart = $originalConsignmentNumberStart;
        return $this;
    }

    public function getOriginalConsignmentNumberEnd(): ?int
    {
        return $this->originalConsignmentNumberEnd;
    }

    public function setOriginalConsignmentNumberEnd(?int $originalConsignmentNumberEnd): self
    {
        $this->originalConsignmentNumberEnd = $originalConsignmentNumberEnd;
        return $this;
    }

    public function getNewConsignmentNumberStart(): ?int
    {
        return $this->newConsignmentNumberStart;
    }

    public function setNewConsignmentNumberStart(?int $newConsignmentNumberStart): self
    {
        $this->newConsignmentNumberStart = $newConsignmentNumberStart;
        return $this;
    }

    public function getNewConsignmentNumberEnd(): ?int
    {
        return $this->newConsignmentNumberEnd;
    }

    public function setNewConsignmentNumberEnd(?int $newConsignmentNumberEnd): self
    {
        $this->newConsignmentNumberEnd = $newConsignmentNumberEnd;
        return $this;
    }

    public function getCurrentParcelRange(): string
    {
        return $this->getRange($this->originalParcelNumberStart, $this->originalParcelNumberEnd);
    }

    public function getNewParcelRange(): string
    {
        return $this->getRange($this->newParcelNumberStart, $this->newParcelNumberEnd);
    }

    public function getCurrentConsignmentRange(): string
    {
        return $this->getRange($this->originalConsignmentNumberStart, $this->originalConsignmentNumberEnd);
    }

    public function getNewConsignmentRange(): string
    {
        return $this->getRange($this->newConsignmentNumberStart, $this->newConsignmentNumberEnd);
    }

    protected function getRange(?int $start, ?int $end): string
    {
        return $start . '-' . $end;
    }
}