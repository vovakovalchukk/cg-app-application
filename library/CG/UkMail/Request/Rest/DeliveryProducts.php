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
    /** @var int */
    protected $length;
    /** @var int */
    protected $width;
    /** @var int */
    protected $height;
    /** @var string */
    protected $recipientAddressType;
    /** @var string */
    protected $recipientPostcode;
    /** @var bool */
    protected $doorstepOnly;

    public function __construct(
        string $apiKey,
        string $countryCode,
        float $weight,
        int $length,
        int $width,
        int $height,
        string $recipientAddressType,
        string $recipientPostcode,
        bool $doorstepOnly
    ) {
        $this->apiKey = $apiKey;
        $this->countryCode = $countryCode;
        $this->weight = $weight;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->recipientAddressType = $recipientAddressType;
        $this->recipientPostcode = $recipientPostcode;
        $this->doorstepOnly = $doorstepOnly;
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
        return Response::class;
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
            'countryCode' => $this->getCountryCode(),
            'weight' => $this->getWeight(),
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'recipientAddressType' => $this->getRecipientAddressType(),
            'recipientPostcode' => $this->getRecipientPostcode(),
            'doorstepOnly' => $this->isDoorstepOnly() ? 'true' : 'false',
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

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): DeliveryProducts
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): DeliveryProducts
    {
        $this->weight = $weight;
        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): DeliveryProducts
    {
        $this->length = $length;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): DeliveryProducts
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): DeliveryProducts
    {
        $this->height = $height;
        return $this;
    }

    public function getRecipientAddressType(): string
    {
        return $this->recipientAddressType;
    }

    public function setRecipientAddressType(string $recipientAddressType): DeliveryProducts
    {
        $this->recipientAddressType = $recipientAddressType;
        return $this;
    }

    public function getRecipientPostcode(): string
    {
        return $this->recipientPostcode;
    }

    public function setRecipientPostcode(string $recipientPostcode): DeliveryProducts
    {
        $this->recipientPostcode = $recipientPostcode;
        return $this;
    }

    public function isDoorstepOnly(): bool
    {
        return $this->doorstepOnly;
    }

    public function setDoorstepOnly(bool $doorstepOnly): DeliveryProducts
    {
        $this->doorstepOnly = $doorstepOnly;
        return $this;
    }
}