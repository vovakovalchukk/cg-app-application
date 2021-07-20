<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\AbstractRequest;
use CG\UkMail\Response\Rest\DeliveryProducts as Response;

class DeliveryProducts extends AbstractRequest implements RequestInterface
{
    protected const URI = 'v2/products/parcels/deliveryProducts';

    /** @var string */
    protected $apiKey;
    /** @var string */
    protected $countryCode;
    /** @var float */
    protected $weight;
    protected $length;
    protected $width;


    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        return [
            'headers' => array_merge($options['headers'] ?? [], $this->getHeaders()),
            'query' => array_merge($options['query'] ?? [], $this->getQuery())
        ];
    }

    public function getResponseClass(): string
    {
        // TODO: Implement getResponseClass() method.
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];
    }

    protected function getQuery(): array
    {
        return [
        ];
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): DeliveryProducts
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}