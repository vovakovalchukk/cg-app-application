<?php
namespace CG\RoyalMailApi\Request\Manifest;

use CG\RoyalMailApi\Request\PutAbstract;
use CG\RoyalMailApi\Response\Manifest\PrintManifest as Response;

class PrintManifest extends PutAbstract
{
    const URI = '/manifest';

    /** @var string */
    protected $manifestBatchNumber;

    public function __construct(string $manifestBatchNumber)
    {
        $this->manifestBatchNumber = $manifestBatchNumber;
    }

    protected function toArray(): array
    {
        return [];
    }

    public function getUri(): string
    {
        return static::URI . '?' . http_build_query(['manifestBatchNumber' => $this->manifestBatchNumber]);
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getManifestBatchNumber(): string
    {
        return $this->manifestBatchNumber;
    }
}
