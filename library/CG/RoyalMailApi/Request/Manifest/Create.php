<?php
namespace CG\RoyalMailApi\Request\Manifest;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\Request\PostAbstract;
use CG\RoyalMailApi\Response\Shipment\Create as ResponseCreate;

class Create extends PostAbstract
{
    const URI = 'manifest';

    /** @var ?string */
    protected $serviceOccurence;
    /** @var ?string */
    protected $serviceCode;
    /** @var ?string */
    protected $yourDescription;
    /** @var ?string */
    protected $yourReference;

    public function __construct(
        ?string $serviceOccurence = null,
        ?string $serviceCode = null,
        ?string$yourDescription = null ,
        ?string $yourReference = null
    ) {
        $this->serviceOccurence = $serviceOccurence;
        $this->serviceCode = $serviceCode;
        $this->yourDescription = $yourDescription;
        $this->yourReference = $yourReference;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    public function getResponseClass(): string
    {
        return ResponseCreate::class;
    }

    protected function toArray(): array
    {
        return [
            'serviceOccurence' => $this->getServiceOccurence(),
            'serviceCode' => $this->getServiceCode(),
            'yourDescription' => $this->getYourDescription(),
            'yourReference' => $this->getYourReference()
        ];
    }

    public function getServiceOccurence(): ?string
    {
        return $this->serviceOccurence;
    }

    public function getServiceCode(): ?string
    {
        return $this->serviceCode;
    }

    public function getYourDescription(): ?string
    {
        return $this->yourDescription;
    }

    public function getYourReference(): ?string
    {
        return $this->yourReference;
    }
}
