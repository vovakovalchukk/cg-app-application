<?php
namespace CG\Hermes\Response;

use CG\Hermes\ResponseInterface;
use SimpleXMLElement;

class RouteDeliveryCreatePreadviceAndLabel implements ResponseInterface
{
    /** @var array */
    protected $barcodeNumbers;
    /** @var array */
    protected $labels;
    /** @var array */
    protected $errorMessages = [];
    /** @var array */
    protected $warningMessages = [];

    public function __construct(
        array $barcodeNumbers = [],
        array $labels = [],
        array $errorMessages = [],
        array $warningMessages = []
    ) {
        $this->barcodeNumbers = $barcodeNumbers;
        $this->labels = $labels;
        $this->errorMessages = $errorMessages;
        $this->warningMessages = $warningMessages;
    }

    public static function createFromXml(SimpleXMLElement $xml)
    {
        if (!isset($xml->routingResponseEntries, $xml->routingResponseEntries->routingResponseEntry)) {
            throw new \RuntimeException('RouteDeliveryCreatePreadviceAndLabel response not in expected format');
        }
        $barcodes = [];
        $labels = [];
        $errors = [];
        $warnings = [];
        /** @var SimpleXMLElement $response */
        foreach ($xml->routingResponseEntries->routingResponseEntry as $response) {
            if (isset($response->errorMessages)) {
                $errors = array_merge($errors, static::parseErrorsFromXml($response->errorMessages));
                continue;
            }
            if (isset($response->warningMessages)) {
                $warnings = array_merge($warnings, static::parseErrorsFromXml($response->warningMessages));
            }
            if (!isset($response->outboundCarriers)) {
                continue;
            }
            $labels[] = (string)$response->outboundCarriers->labelImage;
            if (!isset($response->outboundCarriers->carrier1, $response->outboundCarriers->carrier1->barcode1)) {
                continue;
            }
            $barcodes[] = (string)$response->outboundCarriers->carrier1->barcode1->barcodeNumber;
        }
        return new static($barcodes, $labels, $errors, $warnings);
    }

    protected static function parseErrorsFromXml(SimpleXMLElement $errorMessages): array
    {
        $errors = [];
        foreach ($errorMessages as $errorMessage) {
            $errors[(string)$errorMessage->errorCode] = (string)$errorMessage->errorDescription;
        }
        return $errors;
    }

    public function getBarcodeNumbers(): array
    {
        return $this->barcodeNumbers;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }
}