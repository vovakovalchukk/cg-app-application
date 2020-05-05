<?php
namespace Orders\Order\Invoice\Template;

class Selection
{
    /** @var array */
    protected $templateIds;
    /** @var string|null */
    protected $orderBy;

    public function __construct(array $templateIds, ?string $orderBy)
    {
        $this->templateIds = $templateIds;
        $this->orderBy = $orderBy;
    }

    public function getTemplateIds(): array
    {
        return $this->templateIds;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }
}