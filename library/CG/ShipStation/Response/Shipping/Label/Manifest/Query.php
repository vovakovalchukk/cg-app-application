<?php
namespace CG\ShipStation\Response\Shipping\Manifest;

use CG\ShipStation\ResponseAbstract;
use CG\ShipStation\Response\Shipping\Manifest\Create as Manifest;

class Query extends ResponseAbstract
{
    /** @var Manifest[] */
    protected $manifests;
    /** @var int */
    protected $total;
    /** @var int */
    protected $page;
    /** @var int */
    protected $pages;

    public function __construct(array $manifests, int $total, int $page, int $pages)
    {
        $this->manifests = $manifests;
        $this->total = $total;
        $this->page = $page;
        $this->pages = $pages;
    }

    protected static function build($decodedJson)
    {
        $manifests = [];
        foreach ($decodedJson->manifests as $manifestJson) {
            $manifests[] = Manifest::build($manifestJson);
        }
        return new static(
            $manifests,
            $decodedJson->total,
            $decodedJson->page,
            $decodedJson->pages
        );
    }

    /**
     * @return Manifest[]
     */
    public function getManifests(): array
    {
        return $this->manifests;
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