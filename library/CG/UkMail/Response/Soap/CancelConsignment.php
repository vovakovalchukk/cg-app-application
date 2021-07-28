<?php
namespace CG\UkMail\Response\Soap;

use CG\UkMail\Response\ResponseInterface;
use CG\UkMail\Response\AbstractSoapResponse;

class CancelConsignment extends AbstractSoapResponse implements ResponseInterface
{
    protected const RESULT_FAILED = 'Failed';
    protected const RESULT_SUCCESS = 'Successful';

    /** @var string */
    protected $result;
    /** @var bool */
    protected $error;
    /** @var string|null */
    protected $errorCode;
    /** @var string|null */
    protected $errorDescription;

    public function __construct(string $result, bool $error, ?string $errorCode, ?string $errorDescription)
    {
        $this->result = $result;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
    }

    public static function createResponse($response): ResponseInterface
    {
        $hasError = false;
        $errorCode = null;
        $errorDescription = null;

        $dom = new \DOMDocument();
        $dom->loadXML((string)$response);

        $result = $dom->getElementsByTagName('Result');
        $resultValue = $result->item(0)->nodeValue;
        if ($result->length > 0 && $resultValue == static::RESULT_FAILED) {
            $hasError = true;
            $errorCode = $dom->getElementsByTagName('Code')->item(0)->nodeValue;
            $errorDescription = $dom->getElementsByTagName('Description')->item(0)->nodeValue;
        }

        return new static($resultValue, $hasError, $errorCode, $errorDescription);
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @return string|null
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}