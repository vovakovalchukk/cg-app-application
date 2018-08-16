<?php
namespace CG\ShipStation\Response\Shipping\Label;

use CG\ShipStation\ResponseAbstract;
use CG\ShipStation\Response\Shipping\Label;

class Query extends ResponseAbstract
{
    /** @var Label[] */
    protected $labels;
    /** @var int */
    protected $total;
    /** @var int */
    protected $page;
    /** @var int */
    protected $pages;

    public function __construct(array $labels, int $total, int $page, int $pages)
    {
        $this->labels = $labels;
        $this->total = $total;
        $this->page = $page;
        $this->pages = $pages;
    }

    protected static function build($decodedJson)
    {
        $labels = [];
        foreach ($decodedJson->labels as $labelJson) {
            $labels[] = Label::build($labelJson);
        }
        return new static(
            $labels,
            $decodedJson->total,
            $decodedJson->page,
            $decodedJson->pages
        );
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}